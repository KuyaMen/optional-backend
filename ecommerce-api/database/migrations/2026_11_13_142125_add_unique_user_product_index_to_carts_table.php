<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicateProducts = DB::table('carts')
            ->select('user_id', 'product_id')
            ->groupBy('user_id', 'product_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateProducts as $duplicateProduct) {
            $items = DB::table('carts')
                ->where('user_id', $duplicateProduct->user_id)
                ->where('product_id', $duplicateProduct->product_id)
                ->orderBy('id')
                ->get();
            $keeper = $items->first();

            DB::table('carts')->where('id', $keeper->id)->update([
                'quantity' => $items->sum('quantity'),
            ]);
            DB::table('carts')
                ->where('user_id', $duplicateProduct->user_id)
                ->where('product_id', $duplicateProduct->product_id)
                ->where('id', '!=', $keeper->id)
                ->delete();
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_id_product_id_unique');
        });
    }
};
