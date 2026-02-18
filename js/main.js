// Global state to store items for filtering and rendering
let categories = [];
let foodItems = [];
let galleryItems = [];

/**
 * Fetches categories and food items from the backend APIs.
 * This function should be called during page initialization.
 */
async function loadMenuData() {
  showLoader();
  try {
    // Fetch all active categories for the filter buttons
    const catResponse = await fetch('api/category/list.php');
    const catData = await catResponse.json();
    if (catData.success) {
      categories = catData.data;
    }

    // Fetch all food items across all categories
    const foodResponse = await fetch('api/food/list.php');
    const foodData = await foodResponse.json();
    if (foodData.success) {
      foodItems = foodData.data;
    }
  } catch (error) {
    console.error('Failed to load menu data:', error);
  } finally {
    hideLoader();
  }
}

/**
 * Fetches gallery media (images and videos) from the public API.
 */
async function loadGalleryData() {
  showLoader();
  try {
    const response = await fetch('api/gallery/list.php');
    const data = await response.json();
    if (data.success) {
      galleryItems = data.data;
    }
  } catch (error) {
    console.error('Failed to load gallery data:', error);
  } finally {
    hideLoader();
  }
}

const chefs = [
  { id: 1, name: 'Chef Baraka Mwangi', title: 'Head Chef', bio: 'With over 15 years of culinary experience, Chef Baraka brings authentic Tanzanian flavors to every dish. Trained in both traditional and contemporary techniques.', image: 'images/chef-1.jpg' },
  { id: 2, name: 'Chef Amina Hassan', title: 'Sous Chef', bio: 'Chef Amina specializes in fusion cuisine, blending African and international flavors. Her creative desserts are a customer favorite.', image: 'images/chef-2.jpg' },
];

function formatPrice(price) {
  return `TZS ${price.toLocaleString()}`;
}

function getCurrentPage() {
  const path = window.location.pathname;
  const page = path.split('/').pop() || 'index.html';
  return page.replace('.html', '');
}

let cart = JSON.parse(localStorage.getItem('marsla_cart')) || [];

function saveCart() {
  localStorage.setItem('marsla_cart', JSON.stringify(cart));
  updateCartUI();
}

function addToCart(foodItem, openCartAfterAdd = false) {
  const existingItem = cart.find(item => item.food_id === foodItem.food_id);
  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({
      cart_item_id: Date.now(),
      food_id: foodItem.food_id,
      food_name: foodItem.food_name,
      price: foodItem.price,
      quantity: 1,
      image_path: foodItem.image_path,
    });
  }
  saveCart();
  showToast(`${foodItem.food_name} added to cart!`, formatPrice(foodItem.price), 'success');

  if (openCartAfterAdd) {
    openCart();
  }
}

function removeFromCart(cartItemId) {
  cart = cart.filter(item => item.cart_item_id !== cartItemId);
  saveCart();
}

function updateQuantity(cartItemId, quantity) {
  if (quantity <= 0) {
    removeFromCart(cartItemId);
    return;
  }
  const item = cart.find(item => item.cart_item_id === cartItemId);
  if (item) {
    item.quantity = quantity;
    saveCart();
  }
}

function clearCart() {
  cart = [];
  saveCart();
}

function getCartTotal() {
  return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

function getCartItemCount() {
  return cart.reduce((sum, item) => sum + item.quantity, 0);
}

function updateCartUI() {
  const count = getCartItemCount();
  
  document.querySelectorAll('.cart-badge, .mobile-cart-badge').forEach(badge => {
    badge.textContent = count;
    badge.style.display = count > 0 ? 'flex' : 'none';
  });

  const cartItemsContainer = document.getElementById('cart-items');
  const cartFooter = document.getElementById('cart-footer');
  
  if (!cartItemsContainer) return;

  if (cart.length === 0) {
    cartItemsContainer.innerHTML = `
      <div class="cart-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/>
          <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
        </svg>
        <p>Your cart is empty</p>
      </div>
    `;
    if (cartFooter) cartFooter.style.display = 'none';
  } else {
    cartItemsContainer.innerHTML = cart.map(item => `
      <div class="cart-item" data-id="${item.cart_item_id}">
        <div class="cart-item-image">
          <img src="${item.image_path}" alt="${item.food_name}">
        </div>
        <div class="cart-item-details">
          <div class="cart-item-name">${item.food_name}</div>
          <div class="cart-item-price">${formatPrice(item.price)}</div>
          <div class="cart-item-controls">
            <button class="cart-qty-btn" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity - 1})">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>
            </button>
            <span class="cart-qty">${item.quantity}</span>
            <button class="cart-qty-btn" onclick="updateQuantity(${item.cart_item_id}, ${item.quantity + 1})">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
            <button class="cart-item-remove" onclick="removeFromCart(${item.cart_item_id})">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            </button>
          </div>
        </div>
      </div>
    `).join('');
    
    if (cartFooter) {
      cartFooter.style.display = 'block';
      const totalEl = cartFooter.querySelector('.cart-total-amount');
      if (totalEl) totalEl.textContent = formatPrice(getCartTotal());
    }
  }
}

function openCart() {
  document.getElementById('cart-overlay')?.classList.add('open');
  document.getElementById('cart-sidebar')?.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeCart() {
  document.getElementById('cart-overlay')?.classList.remove('open');
  document.getElementById('cart-sidebar')?.classList.remove('open');
  document.body.style.overflow = 'auto';
}

function showToast(title, description = '', type = 'success') {
  const container = document.getElementById('toast-container') || createToastContainer();
  
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <svg class="toast-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      ${type === 'success' 
        ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'
        : '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/>'
      }
    </svg>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      ${description ? `<div class="toast-description">${description}</div>` : ''}
    </div>
    <button class="toast-close" onclick="this.parentElement.remove()">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
  `;
  
  container.appendChild(toast);
  
  setTimeout(() => {
    toast.classList.add('toast-exit');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

function createToastContainer() {
  const container = document.createElement('div');
  container.id = 'toast-container';
  container.className = 'toast-container';
  document.body.appendChild(container);
  return container;
}

function initNavigation() {
  const navbar = document.querySelector('.navbar');
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  const currentPage = getCurrentPage();

  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar?.classList.add('scrolled');
    } else {
      navbar?.classList.remove('scrolled');
    }
  });

  hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    mobileMenu?.classList.toggle('open');
  });

  document.querySelectorAll('.nav-link, .mobile-menu-link').forEach(link => {
    const href = link.getAttribute('href');
    const linkPage = href?.replace('.html', '').replace('./', '') || 'index';
    if (linkPage === currentPage || (currentPage === 'index' && linkPage === './')) {
      link.classList.add('active');
    }
  });

  document.querySelectorAll('.cart-button, .mobile-cart-button').forEach(btn => {
    btn.addEventListener('click', openCart);
  });

  document.getElementById('cart-overlay')?.addEventListener('click', closeCart);
  document.querySelector('.cart-close')?.addEventListener('click', closeCart);
  document.querySelector('.cart-clear')?.addEventListener('click', clearCart);
}

function initHeroCarousel() {
  const carousel = document.querySelector('.hero-carousel');
  if (!carousel) return;

  const images = carousel.querySelectorAll('img');
  const dots = carousel.querySelectorAll('.carousel-dot');
  let currentIndex = 0;

  function showSlide(index) {
    images.forEach((img, i) => {
      img.classList.toggle('active', i === index);
    });
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === index);
    });
    currentIndex = index;
  }

  setInterval(() => {
    showSlide((currentIndex + 1) % images.length);
  }, 6000);

  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => showSlide(i));
  });

  showSlide(0);
}

function initScrollReveal() {
  const reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
      }
    });
  }, { threshold: 0.1 });

  reveals.forEach(el => observer.observe(el));
}

function initMenuPage() {
  const menuGrid = document.getElementById('menu-grid');
  const searchInput = document.getElementById('menu-search');
  const categoryBtns = document.querySelectorAll('.category-btn');
  
  if (!menuGrid) return;

  let selectedCategory = null;
  let searchQuery = '';

  function renderMenu() {
    const filtered = foodItems.filter(item => {
      const matchesSearch = item.food_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.description.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesCategory = selectedCategory === null || item.category_id === selectedCategory;
      return matchesSearch && matchesCategory;
    });

    if (filtered.length === 0) {
      menuGrid.innerHTML = '<div class="no-results">No dishes found matching your search.</div>';
      return;
    }

    menuGrid.innerHTML = filtered.map(item => {
      const category = categories.find(c => c.category_id === item.category_id);
      const isAvailable = item.availability_status === 'Available';
      
      return `
        <div class="menu-card">
          <div class="menu-card-image">
            <img src="${item.image_path}" alt="${item.food_name}">
            ${!isAvailable ? '<div class="menu-card-overlay"><span>Currently Unavailable</span></div>' : ''}
            <div class="menu-badges">
              <span class="badge badge-secondary">${category?.category_name || 'Uncategorized'}</span>
              <span class="badge ${isAvailable ? 'badge-available' : 'badge-unavailable'}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  ${isAvailable 
                    ? '<polyline points="20 6 9 17 4 12"/>'
                    : '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>'
                  }
                </svg>
                ${item.availability_status}
              </span>
            </div>
          </div>
          <div class="menu-card-content">
            <div class="menu-card-header">
              <h3 class="menu-card-name">${item.food_name}</h3>
              <span class="menu-card-price">${formatPrice(item.price)}</span>
            </div>
            <p class="menu-card-description">${item.description}</p>
            <div class="menu-card-actions">
              <button class="btn-outline" ${!isAvailable ? 'disabled' : ''} onclick='addToCart(${JSON.stringify(item).replace(/'/g, "\\'")}, false)'>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Add to Cart
              </button>
              <button class="btn-primary" ${!isAvailable ? 'disabled' : ''} onclick='addToCart(${JSON.stringify(item).replace(/'/g, "\\'")}, true)'>
                Order Now
              </button>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  // Render category buttons dynamically
  // Render category buttons dynamically in the category-filters container
  const categoryContainer = document.querySelector('.category-filters');
  if (categoryContainer) {
    categoryContainer.innerHTML = '';
    const allBtn = document.createElement('button');
    allBtn.className = 'category-btn active';
    allBtn.dataset.category = '';
    allBtn.textContent = 'All';
    categoryContainer.appendChild(allBtn);

    categories.forEach(cat => {
      const b = document.createElement('button');
      b.className = 'category-btn';
      b.dataset.category = String(cat.category_id);
      b.textContent = cat.category_name;
      categoryContainer.appendChild(b);
    });
  }

  // Refresh categoryBtns reference and attach listeners
  const newCategoryBtns = document.querySelectorAll('.category-btn');
  newCategoryBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      newCategoryBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedCategory = btn.dataset.category ? parseInt(btn.dataset.category) : null;
      renderMenu();
    });
  });

  searchInput?.addEventListener('input', (e) => {
    searchQuery = e.target.value;
    renderMenu();
  });

  categoryBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      categoryBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedCategory = btn.dataset.category ? parseInt(btn.dataset.category) : null;
      renderMenu();
    });
  });

  renderMenu();
}

function initGalleryPage() {
  const galleryGrid = document.getElementById('gallery-grid');
  const filterBtns = document.querySelectorAll('.gallery-filter-btn');
  const lightbox = document.getElementById('lightbox');
  
  if (!galleryGrid) return;

  let currentFilter = 'all';
  let currentIndex = 0;
  let filteredItems = galleryItems;

  function renderGallery() {
    filteredItems = currentFilter === 'all' 
      ? galleryItems 
      : galleryItems.filter(item => item.media_type === currentFilter);

    if (filteredItems.length === 0) {
      galleryGrid.innerHTML = '<div class="no-results">No items found in this category.</div>';
      return;
    }

    galleryGrid.innerHTML = filteredItems.map((item, index) => `
      <div class="gallery-item reveal reveal-up active" onclick="openLightbox(${index})">
        ${item.media_type === 'video' 
          ? `<video src="${item.image_path}" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>`
          : `<img src="${item.image_path}" alt="${item.title}" loading="lazy">`
        }
        <div class="gallery-item-overlay"></div>
        <div class="gallery-item-content">
          <h3 class="gallery-item-title">${item.title}</h3>
          <p class="gallery-item-description">${item.caption || ''}</p>
        </div>
        <span class="gallery-type-badge ${item.media_type}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            ${item.media_type === 'video'
              ? '<polygon points="5 3 19 12 5 21 5 3"/>'
              : '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'
            }
          </svg>
          ${item.media_type.toUpperCase()}
        </span>
      </div>
    `).join('');
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.dataset.filter;
      renderGallery();
    });
  });

  window.openLightbox = function(index) {
    currentIndex = index;
    showLightboxItem();
    lightbox?.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeLightbox = function() {
    lightbox?.classList.remove('open');
    document.body.style.overflow = 'auto';

    const video = lightbox?.querySelector('video');
    if (video) video.pause();
  };

  window.navigateLightbox = function(direction) {
    currentIndex = direction === 'next'
      ? (currentIndex + 1) % filteredItems.length
      : (currentIndex - 1 + filteredItems.length) % filteredItems.length;
    showLightboxItem();
  };

  function showLightboxItem() {
    const item = filteredItems[currentIndex];
    const mediaContainer = document.getElementById('lightbox-media');
    const title = document.getElementById('lightbox-title');
    const description = document.getElementById('lightbox-description');
    const badge = document.getElementById('lightbox-badge');
    const counter = document.getElementById('lightbox-counter');

    if (item.media_type === 'video') {
      mediaContainer.innerHTML = `<video class="lightbox-media" src="${item.image_path}" controls autoplay></video>`;
    } else {
      mediaContainer.innerHTML = `<img class="lightbox-media" src="${item.image_path}" alt="${item.title}">`;
    }

    badge.className = `lightbox-badge ${item.media_type}`;
    badge.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        ${item.media_type === 'video'
          ? '<polygon points="5 3 19 12 5 21 5 3"/>'
          : '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>'
        }
      </svg>
      ${item.media_type.toUpperCase()}
    `;
    title.textContent = item.title;
    description.textContent = item.caption || '';
    counter.textContent = `${currentIndex + 1} / ${filteredItems.length}`;
  }

  document.addEventListener('keydown', (e) => {
    if (!lightbox?.classList.contains('open')) return;
    if (e.key === 'ArrowLeft') navigateLightbox('prev');
    if (e.key === 'ArrowRight') navigateLightbox('next');
    if (e.key === 'Escape') closeLightbox();
  });

  lightbox?.addEventListener('click', (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  renderGallery();
}

function initContactPage() {
  const form = document.getElementById('contact-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalContent = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <span class="spinner"></span>
      Sending...
    `;

    await new Promise(resolve => setTimeout(resolve, 1500));

    showToast('Message sent successfully!', 'We will get back to you soon.', 'success');
    form.reset();
    
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalContent;
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  initNavigation();
  initHeroCarousel();
  initScrollReveal();
  await loadMenuData();
  await loadGalleryData();
  initMenuPage();
  initGalleryPage();
  initContactPage();
  updateCartUI();
});

document.querySelectorAll('.dropdown-menu').forEach(menu => {
  menu.addEventListener('click', e => e.stopPropagation());
});

const footer = document.querySelector('.footer');
  if (footer) {
    const footerObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            footer.classList.add('visible');
            observer.unobserve(footer);
            }
          });
      }, { threshold: 0.2 }
    );
  footerObserver.observe(footer);
}

const yearElement = document.getElementById('current-year');
  if (yearElement) {
  yearElement.textContent = new Date().getFullYear();
}