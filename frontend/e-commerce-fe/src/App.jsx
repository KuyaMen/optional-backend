import { useState } from 'react'
import heroImg from './assets/hero.png'
import reactLogo from './assets/react.svg'
import viteLogo from './assets/vite.svg'
import './App.css'


  const products = [
  {
    id: 1,
    name: 'Matcha frappe',
    price: 179,
    description: 'A rich and creamy blend of premium matcha green tea, milk, and ice, topped with a velvety layer of whipped cream.',
    image: 'https://i.pinimg.com/1200x/78/a2/73/78a273c1a673c685651c27fd007030ca.jpg',
    imageAlt: 'Matcha frappe placeholder',
  },
  {
    id: 2,
    name: 'Black Coffee',
    price:150,
    description: 'A classic, dark roast brewed espresso or drip coffee delivering a bold, robust aroma and clean finish.',
    image: 'https://i.pinimg.com/736x/d6/f7/3c/d6f73c6fda41c861b83c282cca15d59b.jpg',
    imageAlt: 'Black Coffee placeholder',
  },
  {
    id: 3,
    name: 'Biscoff Cheesecake',
    price: 295,
    description: 'Is a rich, creamy dessert featuring the warm, caramelized spice flavors of Lotus Biscoff cookies and cookie butter.',
    image: 'https://i.pinimg.com/736x/af/9f/26/af9f2633ee6b831158eda15ad151a18d.jpg',
    imageAlt: 'French Fries placeholder',
  },
];

function App() {
  return (
    <main className="page">
      <header className="site-header">
        <p className="eyebrow">WELCOME</p>
        <h1>FEATURED PRODUCTS</h1>
        <p>Simple finds for your everyday needs.</p>
      </header>

      <section className="product-section" aria-label="Product listing">
        <div className="section-heading">
          <h2>Our Products</h2>
          <p>{products.length} products available</p>
        </div>

        <div className="product-grid">
          {products.map((product) => (
            <article className="product-card" key={product.id}>
              <img
                className="product-image"
                src={product.image}
                alt={product.imageAlt}
              />
              <div className="product-details">
                <h3>{product.name}</h3>
                <p className="description">{product.description}</p>
                <p className="price">
                  {product.price.toLocaleString('en-PH', {
                    style: 'currency',
                    currency: 'PHP',
                  })}
                </p>
                <button
                  type="button"
                  onClick={() => alert(`${product.name} added to cart!`)}
                >
                  Add to Cart
                </button>
              </div>
            </article>
          ))}
        </div>
      </section>

      <footer className="site-footer">
        <p>Student activity • React + JSX</p>
      </footer>
    </main>
  );
}

export default App;

  