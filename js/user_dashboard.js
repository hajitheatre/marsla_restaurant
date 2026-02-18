let userOrders = [];

// Load user's orders from API
async function loadOrders() {
  showLoader();
  try {
    const response = await fetch("../api/user/orders.php");
    const data = await response.json();

    if (data.success && data.data) {
      userOrders = data.data;
      console.log("✓ Orders loaded:", userOrders.length, "orders");
    } else {
      console.warn("No orders found:", data.message);
      userOrders = [];
    }
    renderOrders();
  } catch (error) {
    console.error("Error loading orders:", error);
    userOrders = [];
  } finally {
    hideLoader();
  }
}

// Load dashboard stats from API
async function loadDashboardStats() {
  try {
    const response = await fetch("../api/user/dashboard-stats.php");
    const data = await response.json();
    if (data.success) {
      // Format values
      const totalOrders = data.totalOrders || 0;
      const totalSpent = data.totalSpent || 0;
      let lastOrder = "No orders yet";

      if (data.lastOrderDate) {
        const date = new Date(data.lastOrderDate);
        lastOrder =
          date.toLocaleDateString("en-US", { month: "short", day: "numeric" }) +
          ", " +
          date.toLocaleTimeString("en-US", {
            hour: "2-digit",
            minute: "2-digit",
          });
      }

      // Update DOM
      document.getElementById("totalOrdersValue").textContent = totalOrders;
      document.getElementById("totalSpentValue").textContent =
        "TZS " + totalSpent.toLocaleString();
      document.getElementById("lastLoginValue").textContent = lastOrder;
    }
  } catch (error) {
    console.error("Error loading dashboard stats:", error);
  }
}

// Load menu data for the dashboard
async function loadDashboardMenuData() {
  showLoader();
  console.log("=== Starting loadDashboardMenuData ===");
  try {
    // Fetch categories
    const catResponse = await fetch("../api/category/list.php");
    if (!catResponse.ok)
      throw new Error(`Category API error: ${catResponse.status}`);
    const catData = await catResponse.json();
    if (catData.success && catData.data) {
      categories = catData.data;
      console.log("✓ Categories loaded:", categories.length, categories);
    } else {
      throw new Error(
        "Categories API returned no data: " + JSON.stringify(catData),
      );
    }

    // Fetch food items
    const foodResponse = await fetch("../api/food/list.php");
    if (!foodResponse.ok)
      throw new Error(`Food API error: ${foodResponse.status}`);
    const foodData = await foodResponse.json();
    if (foodData.success && foodData.data) {
      foodItems = foodData.data;
      console.log("✓ Food items loaded:", foodItems.length, "items");
      if (foodItems.length > 0) {
        console.log("  First item:", foodItems[0]);
      }
    } else {
      throw new Error("Food API returned no data: " + JSON.stringify(foodData));
    }

    // Initialize menu display
    console.log("Rendering categories and menu...");
    renderCategories();
    renderDashboardMenu();
    console.log("=== loadDashboardMenuData complete ===");
  } catch (error) {
    console.error("✗ Failed to load menu data:", error);
    const menuGrid = document.getElementById("dashboard-menu-grid");
    if (menuGrid) {
      menuGrid.innerHTML = `<div class="no-results">
                <p>Failed to load menu</p>
                <p style="font-size: 0.875rem; color: var(--muted-foreground);">${error.message}</p>
                <p style="font-size: 0.75rem;">Check browser console for details. Verify database is seeded.</p>
            </div>`;
    }
  }
}

// Render category buttons for the dashboard menu
function renderCategories() {
  const categoryContainer = document.getElementById("dashboardCategoryFilters");
  if (!categoryContainer) {
    console.error("Category filters container not found");
    return;
  }

  console.log(
    "renderCategories called. Categories available:",
    categories.length,
  );

  categoryContainer.innerHTML = "";
  const allBtn = document.createElement("button");
  allBtn.className = "category-btn active";
  allBtn.dataset.category = "";
  allBtn.textContent = "All";
  categoryContainer.appendChild(allBtn);

  categories.forEach((cat) => {
    console.log("Creating button for category:", cat.category_name);
    const btn = document.createElement("button");
    btn.className = "category-btn";
    btn.dataset.category = String(cat.category_id);
    btn.textContent = cat.category_name;
    categoryContainer.appendChild(btn);
  });

  // Attach event listeners to category buttons
  const categoryBtns = document.querySelectorAll(
    "#dashboardCategoryFilters .category-btn",
  );
  console.log("Category buttons created:", categoryBtns.length);

  categoryBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      categoryBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      dashboardMenuSelectedCategory = btn.dataset.category
        ? parseInt(btn.dataset.category)
        : null;
      renderDashboardMenu();
    });
  });
}

function renderDashboardMenu() {
  const menuGrid = document.getElementById("dashboard-menu-grid");
  if (!menuGrid) {
    console.error("Menu grid element not found");
    return;
  }

  console.log(
    "renderDashboardMenu called. foodItems:",
    foodItems.length,
    "categories:",
    categories.length,
  );

  if (!foodItems || foodItems.length === 0) {
    console.log("No food items available");
    menuGrid.innerHTML =
      '<div class="no-results">No dishes available. Please check back later.</div>';
    return;
  }

  const filtered = foodItems.filter((item) => {
    const matchesSearch =
      item.food_name
        .toLowerCase()
        .includes(dashboardMenuSearchQuery.toLowerCase()) ||
      item.description
        .toLowerCase()
        .includes(dashboardMenuSearchQuery.toLowerCase());
    const matchesCategory =
      dashboardMenuSelectedCategory === null ||
      item.category_id === dashboardMenuSelectedCategory;
    return matchesSearch && matchesCategory;
  });

  console.log("Filtered items:", filtered.length);

  if (filtered.length === 0) {
    menuGrid.innerHTML =
      '<div class="no-results">No dishes found matching your search.</div>';
    return;
  }

  menuGrid.innerHTML = filtered
    .map((item) => {
      const category = categories.find(
        (c) => c.category_id === item.category_id,
      );
      const isAvailable = item.availability_status === "Available";

      return `
            <div class="menu-card">
                <div class="menu-card-image">
                    <img src="../${item.image_path}" alt="${item.food_name}" style="width: 100%; height: 100%; object-fit: cover;">
                    ${!isAvailable ? '<div class="menu-card-overlay"><span>Currently Unavailable</span></div>' : ""}
                    <div class="menu-badges">
                        <span class="badge badge-secondary">${category?.category_name || "Uncategorized"}</span>
                        <span class="badge ${isAvailable ? "badge-available" : "badge-unavailable"}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                ${
                                  isAvailable
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
                        <button class="btn-outline" ${!isAvailable ? "disabled" : ""} onclick='addDashboardCartItem(${JSON.stringify(item).replace(/'/g, "\\'")}, false)'>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add to Cart
                        </button>
                        <button class="btn-primary" ${!isAvailable ? "disabled" : ""} onclick='addDashboardCartItem(${JSON.stringify(item).replace(/'/g, "\\'")}, true)'>
                            Order Now
                        </button>
                    </div>
                </div>
            </div>
        `;
    })
    .join("");
}

let cart = [];
let currentSlide = 0;
let sliderInterval;
let categories = [];
let foodItems = [];
let dashboardMenuSearchQuery = "";
let dashboardMenuSelectedCategory = null;

// Load cart from localStorage (unified storage)
function initCart() {
  // Try to migrate guest cart first
  const guestCart = sessionStorage.getItem("marsla_guest_cart_backup");
  const savedCart = localStorage.getItem("marsla_cart");

  if (guestCart && !savedCart) {
    // Migrate guest cart to logged-in user's cart
    try {
      const migrated = JSON.parse(guestCart);
      cart = migrated;
      localStorage.setItem("marsla_cart", JSON.stringify(cart));
      sessionStorage.removeItem("marsla_guest_cart_backup");
      console.log("✓ Migrated guest cart with", cart.length, "items");
    } catch (e) {
      cart = [];
    }
  } else if (savedCart) {
    // Load existing cart
    try {
      cart = JSON.parse(savedCart);
    } catch (e) {
      cart = [];
    }
  }
}

// Save cart to localStorage (unified storage)
function saveCart() {
  localStorage.setItem("marsla_cart", JSON.stringify(cart));
  updateCartUI();
}

function formatPrice(price) {
  return `TZS ${price.toLocaleString()}`;
}

function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  const toastMessage = document.getElementById("toastMessage");

  toastMessage.textContent = message;

  // Remove previous type classes
  toast.classList.remove("toast-success", "toast-error");

  // Add appropriate type class
  if (type === "error") {
    toast.classList.add("toast-error");
  } else {
    toast.classList.add("toast-success");
  }

  toast.classList.add("active");

  setTimeout(() => {
    toast.classList.remove("active");
  }, 3000);
}

function showPage(pageName) {
  document.querySelectorAll(".page").forEach((page) => {
    page.classList.remove("active");
  });

  document.getElementById(`page-${pageName}`).classList.add("active");

  document.querySelectorAll(".nav-link").forEach((link) => {
    link.classList.remove("active");
    if (link.dataset.page === pageName) {
      link.classList.add("active");
    }
  });

  document.getElementById("mobileMenu").classList.remove("active");
  // Reset icon to Hamburger
  const icon = document.getElementById("menuIcon");
  if (icon) {
    icon.innerHTML =
      '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
  }

  // If menu page is shown, ensure data is loaded
  if (pageName === "menu") {
    console.log("Menu page activated");
    if (foodItems.length === 0) {
      console.log("Food items not loaded yet, reloading...");
      loadDashboardMenuData();
    } else {
      console.log("Food items already loaded:", foodItems.length);
      renderDashboardMenu();
    }
  }

  window.scrollTo(0, 0);
}



function handleLogout() {
  // Directly navigate to logout script - much faster than fetch + redirect
  window.location.href = "../logout.php";
}

async function initSlider() {
  const track = document.getElementById("sliderTrack");
  const dotsContainer = document.getElementById("sliderDots");
  
  if (!track || !dotsContainer) return;

  try {
    const response = await fetch("../api/offers/list.php");
    const res = await response.json();
    
    if (res.success && res.data && res.data.length > 0) {
      // Clear existing static slides if any
      track.innerHTML = "";
      dotsContainer.innerHTML = "";

      res.data.forEach((offer, index) => {
        // Create slide
        const slide = document.createElement("div");
        slide.className = "slide";
        slide.innerHTML = `
          <img src="../${offer.image_path}" alt="${offer.title}">
          <div class="slide-overlay">
            <h3>${offer.title}</h3>
            <p>${offer.caption}</p>
          </div>
        `;
        track.appendChild(slide);

        // Create dot
        const dot = document.createElement("button");
        dot.className = `slider-dot ${index === 0 ? "active" : ""}`;
        dot.onclick = () => goToSlide(index);
        dotsContainer.appendChild(dot);
      });

      startSliderInterval();
    } else {
      // Hide section if no offers
      const promoSection = document.querySelector(".promo-section");
      if (promoSection) promoSection.style.display = "none";
    }
  } catch (err) {
    console.error("Failed to load offers", err);
  }
}

function startSliderInterval() {
  sliderInterval = setInterval(() => {
    nextSlide();
  }, 5000);
}

function stopSliderInterval() {
  clearInterval(sliderInterval);
}

function goToSlide(index) {
  const slides = document.querySelectorAll(".slide");
  currentSlide = index;

  document.getElementById("sliderTrack").style.transform =
    `translateX(-${currentSlide * 100}%)`;

  document.querySelectorAll(".slider-dot").forEach((dot, i) => {
    dot.classList.toggle("active", i === currentSlide);
  });

  stopSliderInterval();
  startSliderInterval();
}

function prevSlide() {
  const slides = document.querySelectorAll(".slide");
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  goToSlide(currentSlide);
}

function nextSlide() {
  const slides = document.querySelectorAll(".slide");
  currentSlide = (currentSlide + 1) % slides.length;
  goToSlide(currentSlide);
}

function renderOrders() {
  const tbody = document.getElementById("ordersTableBody");

  if (userOrders.length === 0) {
    tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 4rem 2rem; color: var(--muted-foreground); font-size: 1.1rem; opacity: 0.7;">
                    No orders yet. Start ordering to see your history!
                </td>
            </tr>
        `;
    return;
  }

  tbody.innerHTML = userOrders
    .map((order, index) => {
      // Format date
      const orderDate = new Date(order.date);
      const dateFormatted = orderDate.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
      });

      return `
            <tr>

                <td style="color: var(--muted-foreground);">${dateFormatted}</td>
                <td>${order.items}</td>
                <td class="text-right" style="font-weight: 500;">${formatPrice(order.total_amount)}</td>
                <td class="text-center">
                    <span class="status-badge status-${order.status}">${order.status}</span>
                </td>

            </tr>
        `;
    })
    .join("");
}

async function cancelOrder(orderId) {
  if (!confirm("Are you sure you want to cancel this order?")) return;

  try {
    const response = await fetch("../api/user/orders/cancel.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: orderId }),
    });
    const res = await response.json();

    if (res.success) {
      showToast("Order cancelled successfully", "success");
      loadOrders();
    } else {
      showToast(res.message || "Failed to cancel order", "error");
    }
  } catch (e) {
    console.error(e);
    showToast("Error cancelling order", "error");
  }
}

function toggleCart() {
  document.getElementById("cartOverlay").classList.toggle("active");
  document.getElementById("cartSidebar").classList.toggle("active");
}

function addToCart(itemId) {
  const item = menuItems.find((m) => m.id === itemId);
  if (!item) return;

  const existingItem = cart.find((c) => c.id === itemId);
  if (existingItem) {
    existingItem.quantity++;
  } else {
    cart.push({ ...item, quantity: 1 });
  }

  updateCartUI();
  renderDashboardMenu();
  showToast(`${item.name} added to cart`);
}

// Add item to cart from dashboard menu
function addDashboardCartItem(foodItem, openCartAfterAdd = false) {
  const existingItem = cart.find((item) => item.food_id === foodItem.food_id);
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
  showToast(`${foodItem.food_name} added to cart!`);

  if (openCartAfterAdd) {
    const sidebar = document.getElementById("cartSidebar");
    if (sidebar && !sidebar.classList.contains("active")) {
      toggleCart();
    }
  }
}

function updateCartQuantity(itemId, quantity) {
  if (quantity <= 0) {
    cart = cart.filter((c) => c.food_id !== itemId);
  } else {
    const item = cart.find((c) => c.food_id === itemId);
    if (item) {
      item.quantity = quantity;
    }
  }
  saveCart();
}

function removeFromCart(itemId) {
  cart = cart.filter((c) => c.food_id !== itemId);
  saveCart();
}

function updateCartUI() {
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = cart.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0,
  );

  // Update cart badge in header
  const cartBadge = document.getElementById("cartBadge");
  if (cartBadge) {
    cartBadge.textContent = totalItems;
    cartBadge.style.display = totalItems > 0 ? "flex" : "none";
  }

  // Update cart count in sidebar
  document.getElementById("cartCount").textContent = totalItems;
  document.getElementById("cartTotal").textContent = formatPrice(totalPrice);

  const cartItemsContainer = document.getElementById("cartItems");
  const cartFooter = document.getElementById("cartFooter");

  if (cart.length === 0) {
    cartItemsContainer.innerHTML =
      '<div class="cart-empty"><p>Your cart is empty</p></div>';
    cartFooter.style.display = "none";
  } else {
    cartItemsContainer.innerHTML = cart
      .map(
        (item) => `
            <div class="cart-item">
                <img src="../${item.image_path}" alt="${item.food_name}">
                <div class="cart-item-info">
                    <p class="cart-item-name">${item.food_name}</p>
                    <p class="cart-item-price">${formatPrice(item.price)}</p>
                    <div class="cart-item-controls">
                        <button class="quantity-btn" onclick="updateCartQuantity(${item.food_id}, ${item.quantity - 1})">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/></svg>
                        </button>
                        <span class="quantity-value">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateCartQuantity(${item.food_id}, ${item.quantity + 1})">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        </button>
                        <button class="btn-icon cart-item-remove" onclick="removeFromCart(${item.food_id})" style="color: var(--destructive);">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        `,
      )
      .join("");
    cartFooter.style.display = "block";
  }
}

function showTab(tabName) {
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.classList.toggle("active", tab.dataset.tab === tabName);
  });

  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active");
  });
  document.getElementById(`tab-${tabName}`).classList.add("active");
}

// Global Theme Management
window.toggleTheme = function () {
  const themeSwitch = document.getElementById("themeSwitch");
  if (themeSwitch) {
    setTheme(themeSwitch.checked ? "dark" : "light");
  }
};

window.setTheme = function (theme) {
  const isDark = theme === "dark";
  document.documentElement.setAttribute("data-theme", theme);
  localStorage.setItem("marsla-theme", theme);

  const themeSwitch = document.getElementById("themeSwitch");
  const themeStatus = document.getElementById("themeStatus");
  const themeIcon = document.getElementById("themeIcon");

  if (themeSwitch) themeSwitch.checked = isDark;
  if (themeStatus) {
    themeStatus.textContent = isDark
      ? "Currently using dark theme"
      : "Currently using light theme";
  }

  if (themeIcon) {
    if (isDark) {
      themeIcon.innerHTML = '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>';
    } else {
      themeIcon.innerHTML =
        '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>';
    }
  }

  // Update preview buttons if they exist
  const lightPreview = document.getElementById("lightPreview");
  const darkPreview = document.getElementById("darkPreview");
  if (lightPreview) lightPreview.classList.toggle("active", !isDark);
  if (darkPreview) darkPreview.classList.toggle("active", isDark);
};

async function handleProfileUpdate(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn.textContent;

  const firstName = document.getElementById("firstName").value.trim();
  const lastName = document.getElementById("lastName").value.trim();
  const email = document.getElementById("profileEmail").value.trim();
  const phone = document.getElementById("profilePhone").value.trim();

  // Validation
  if (!firstName) {
    showToast("First name is required", "error");
    return;
  }

  if (firstName.length < 2) {
    showToast("First name must be at least 2 characters", "error");
    return;
  }

  if (!/^[a-zA-Z\s]+$/.test(firstName)) {
    showToast("First name should only contain letters", "error");
    return;
  }

  if (lastName && !/^[a-zA-Z\s]+$/.test(lastName)) {
    showToast("Last name should only contain letters", "error");
    return;
  }

  if (!email) {
    showToast("Email is required", "error");
    return;
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showToast("Please enter a valid email address", "error");
    return;
  }

  if (phone && !/^[0-9+\-\s()]+$/.test(phone)) {
    showToast("Please enter a valid phone number", "error");
    return;
  }

  try {
    btn.disabled = true;
    btn.textContent = "Saving...";

    const response = await fetch("../api/profile/update.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        first_name: firstName,
        last_name: lastName,
        email: email,
        phone: phone,
      }),
    });

    const data = await response.json();
    if (data.success) {
      showToast("Profile updated successfully", "success");
      // Update header names if elements exist
      const headerName = document.querySelector(".user-name");
      if (headerName)
        headerName.textContent = firstName + (lastName ? " " + lastName : "");
    } else {
      showToast(data.message || "Failed to update profile", "error");
    }
  } catch (error) {
    console.error("Profile update error:", error);
    showToast("Error updating profile", "error");
  } finally {
    btn.disabled = false;
    btn.textContent = originalText;
  }
}

async function handlePasswordChange(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type="submit"]');
  const originalText = btn.textContent;

  const currentPassword = document.getElementById("currentPassword").value;
  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Client-side validation
  if (!currentPassword) {
    showToast("Current password is required", "error");
    return;
  }

  if (!newPassword) {
    showToast("New password is required", "error");
    return;
  }

  if (newPassword !== confirmPassword) {
    showToast("New passwords do not match", "error");
    return;
  }

  if (newPassword.length < 8) {
    showToast("Password must be at least 8 characters", "error");
    return;
  }

  if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(newPassword)) {
    showToast(
      "Password must contain uppercase, lowercase, and number",
      "error",
    );
    return;
  }

  try {
    btn.disabled = true;
    btn.textContent = "Updating...";

    const response = await fetch("../api/profile/change_password.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        current_password: currentPassword,
        new_password: newPassword,
        confirm_password: confirmPassword,
      }),
    });

    const data = await response.json();
    if (data.success) {
      showToast("Password changed successfully", "success");
      e.target.reset();
    } else {
      showToast(data.message || "Failed to change password", "error");
    }
  } catch (error) {
    console.error("Password change error:", error);
    showToast("Error changing password", "error");
  } finally {
    btn.disabled = false;
    btn.textContent = originalText;
  }
}

// Password visibility toggle logic
window.togglePasswordVisibility = function (inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const btn = input.parentElement.querySelector(".password-toggle");
  if (!btn) return;
  const svg = btn.querySelector("svg");

  if (input.type === "password") {
    input.type = "text";
    // Eye-off icon
    svg.innerHTML =
      '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
  } else {
    input.type = "password";
    // Eye icon
    svg.innerHTML =
      '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
};

// Checkout function - creates order from cart
async function checkoutCart() {
  if (cart.length === 0) {
    showToast("Your cart is empty");
    return;
  }

  const checkoutBtn = document.querySelector(".checkout-btn");
  const originalText = checkoutBtn?.textContent || "Complete Order";

  // Use a finally block to ensure button is reset even if errors occur
  try {
    // Disable button during submission
    if (checkoutBtn) {
      checkoutBtn.disabled = true;
      checkoutBtn.textContent = "Processing...";
    }

    const response = await fetch("../api/orders/create.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        items: cart,
        phone: document.getElementById("phone")?.value || "",
      }),
    });

    // Check for non-JSON responses (e.g. PHP errors)
    const text = await response.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      throw new Error(
        "Server returned invalid response: " + text.substring(0, 100),
      );
    }

    if (!data.success) {
      throw new Error(data.message || "Unknown error");
    }

    // Order successful - clear cart and show confirmation
    showToast("Order #" + data.order_id + " created successfully!", "success");
    cart = [];
    localStorage.removeItem("marsla_cart");
    updateCartUI();

    // Close cart and reload orders
    setTimeout(() => {
      document.getElementById("cartOverlay")?.classList.remove("active");
      document.getElementById("cartSidebar")?.classList.remove("active");
      showPage("orders");
      loadOrders(); // Fetch updated orders from API
    }, 1500);
  } catch (error) {
    console.error("Checkout error:", error);
    showToast(error.message || "Network error. Please try again.", "error");
  } finally {
    // params: reset button state if it exists and we aren't navigating away immediately
    // (though in success case we wait 1.5s, it's safer to re-enable so user doesn't feel stuck if they stay on page)
    if (checkoutBtn) {
      checkoutBtn.disabled = false;
      checkoutBtn.textContent = originalText;
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("marsla-theme") || "light";
  setTheme(savedTheme);

  initCart();
  initSlider();
  loadOrders(); // Load real orders from API
  updateCartUI();
  loadDashboardStats();
  loadDashboardMenuData();

  // Add event listener for menu search
  const searchInput = document.getElementById("dashboard-menu-search");
  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      dashboardMenuSearchQuery = e.target.value;
      renderDashboardMenu();
    });
  }

  // Real-time updates for user
  // Poll orders every 5 seconds to show status changes
  setInterval(() => {
    loadOrders();
  }, 5000);

  // Poll dashboard stats every 15 seconds
  setInterval(() => {
    loadDashboardStats();
  }, 15000);
});

// Mobile menu toggle
function toggleMobileMenu() {
  const menu = document.getElementById("mobileMenu");
  const icon = document.getElementById("menuIcon");
  menu.classList.toggle("active");

  // Toggle icon between Hamburger and X
  if (menu.classList.contains("active")) {
    // X icon
    icon.innerHTML = '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
  } else {
    // Hamburger icon
    icon.innerHTML =
      '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
  }
}

// Close mobile menu when clicking outside
document.addEventListener("click", (e) => {
  const menu = document.getElementById("mobileMenu");
  const btn = document.querySelector(".mobile-menu-btn");

  if (
    menu &&
    menu.classList.contains("active") &&
    !menu.contains(e.target) &&
    !btn.contains(e.target)
  ) {
    menu.classList.remove("active");
    // Reset icon
    const icon = document.getElementById("menuIcon");
    if (icon) {
      icon.innerHTML =
        '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
    }
  }
});
