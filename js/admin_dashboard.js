/**
 * Fetches food items from the backend, optionally filtered by a search query.
 * Maps the database structure to the frontend model and triggers a re-render.
 * 
 * @param {string} filter - Search query string.
 */
async function loadFoodItems(filter = "") {
  showLoader();
  try {
    const params = new URLSearchParams();
    if (filter) params.set("search", filter);

    const response = await fetch("../api/food/list.php?" + params.toString(), {
      credentials: "same-origin",
    });
    const data = await response.json();

    if (data.success && data.data) {
      foodItems = data.data.map((f) => ({
        id: String(f.food_id),
        name: f.food_name,
        price: f.price,
        category: f.category_name || "Uncategorized",
        category_id: String(f.category_id || ""),
        description: f.description,
        available: f.availability_status === "Available",
        image: f.image_path,
      }));
      renderFoodItems();
    }
  } catch (error) {
    console.error("Failed to load food items:", error);
  } finally {
    hideLoader();
  }
}

// Load categories from database
async function loadCategories() {
  showLoader();
  try {
    const response = await fetch("../api/category/list.php", {
      credentials: "same-origin",
    });
    const data = await response.json();

    if (data.success && data.data) {
      categories = data.data.map((c) => ({
        id: String(c.category_id),
        name: c.category_name,
        itemCount: c.itemCount,
      }));
      renderCategories();
      updateFoodCategorySelect();
    }
  } catch (error) {
    console.error("Failed to load categories:", error);
  } finally {
    hideLoader();
  }
}

let orders = [];

function loadOrders(filter = "", status = "all") {
  fetch("../api/admin/orders/list.php", { credentials: "same-origin" })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        orders = (res.data || []).map((o) => ({
          ...o,
          total: Number(o.total_amount || o.total || 0),
          items: o.items || [], // Ensure items is an array (or string if API returns string)
        }));
        renderOrders(filter, status);
      } else {
        console.error("Failed to load orders:", res.message);
      }
    })
    .catch((err) => console.error("Error loading orders:", err));
}

// Initial load
loadOrders();

// Polling interval identifiers - store globally to clear if needed
window.orderPollingInterval = null;
window.userPollingInterval = null;
window.activityPollingInterval = null;

// Poll for dashboard activity/stats every 2 seconds for "real-time" feel
setInterval(() => {
  renderDashboard();
  loadDashboardStats();
}, 2000);

/**
 * Periodically fetches new orders from the server unless the user is interacting with the UI.
 * This ensures the dashboard feels real-time without interrupting active edits.
 */
function startOrderPolling() {
  if (window.orderPollingInterval) clearInterval(window.orderPollingInterval);

  window.orderPollingInterval = setInterval(() => {
    // Check if user is actively typing in search or has a modal open to prevent refreshing state under them
    const isTyping =
      document.activeElement &&
      (document.activeElement.id === "orderSearch" ||
        document.activeElement.tagName === "INPUT");
    const hasActiveModal = document.querySelector(".modal.active");

    // Only poll if not typing and no modal is blocking view
    if (!hasActiveModal) {
      const searchInput = document.getElementById("orderSearch");
      const statusSelect = document.getElementById("orderStatusFilter");

      const currentFilter = searchInput ? searchInput.value : "";
      const currentStatus = statusSelect ? statusSelect.value : "all";

      loadOrders(currentFilter, currentStatus);
    }
  }, 2000); // 2 seconds
}

// Start polling
startOrderPolling();

function loadDashboardStats() {
  fetch("../api/admin/stats.php?t=" + Date.now(), {
    credentials: "same-origin",
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.success && res.data) {
        const stats = res.data;
        const revEl = document.getElementById("statRevenue");
        const ordEl = document.getElementById("statOrders");
        const itmEl = document.getElementById("statItems");
        const usrEl = document.getElementById("statUsers");

        if (revEl)
          revEl.textContent =
            "TZS " +
            Number(stats.totalRevenue).toLocaleString(undefined, {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            });
        if (ordEl) ordEl.textContent = stats.totalOrders;
        if (itmEl) itmEl.textContent = stats.totalItems;
        if (usrEl) usrEl.textContent = stats.totalUsers;
      }
    })
    .catch(console.error);
}

// Re-attach event listeners to update polling context or trigger immediate load
document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("orderSearch");
  const statusSelect = document.getElementById("orderStatusFilter");

  if (searchInput) {
    searchInput.addEventListener("input", (e) => {
      loadOrders(e.target.value, statusSelect ? statusSelect.value : "all");
    });
  }

  if (statusSelect) {
    statusSelect.addEventListener("change", (e) => {
      loadOrders(searchInput ? searchInput.value : "", e.target.value);
    });
  }

  // Initial stats load
  loadDashboardStats();

  // Export activity history
  const exportBtn = document.getElementById("viewFullHistoryBtn");
  if (exportBtn) {
    exportBtn.addEventListener("click", () => {
      window.location.href = "../api/admin/activities/export.php";
    });

    const clearBtn = document.getElementById("clearRecentsBtn");
    const clearModal = document.getElementById("clearActivitiesModal");
    const confirmClearBtn = document.getElementById("confirmClearBtn");
    const cancelClearBtn = document.getElementById("cancelClearBtn");

    if (clearBtn && clearModal) {
      clearBtn.addEventListener("click", () => {
        clearModal.classList.add("active");
      });

      cancelClearBtn.addEventListener("click", () => {
        clearModal.classList.remove("active");
      });

      // Close on overlay click
      clearModal.addEventListener("click", (e) => {
        if (e.target === clearModal) {
          clearModal.classList.remove("active");
        }
      });

      confirmClearBtn.addEventListener("click", () => {
        // Find the maximum ID in the current activities displayed
        const tbody = document.getElementById("activityTableBody");
        const rows = tbody.querySelectorAll(".activity-row");
        
        // We can get the data again from the state or just fetch it. 
        // More robust: Fetch first or use a global variable.
        // Actually, we can just hide it and save the 'now' timestamp or the max ID we just fetched.
        
        fetch("../api/admin/activities/list.php", { credentials: "same-origin" })
          .then(r => r.json())
          .then(res => {
            if (res.success && res.data.length > 0) {
              const maxId = Math.max(...res.data.map(a => parseInt(a.id)));
              localStorage.setItem('lastClearedActivityId', maxId);
              clearModal.classList.remove("active");
              renderDashboard();
            } else {
              clearModal.classList.remove("active");
            }
          });
      });
    }

    // Category Items Modal Listeners
    const closeCategoryBtn = document.getElementById("closeCategoryItemsBtn");
    const categoryModal = document.getElementById("categoryItemsModal");

    if (closeCategoryBtn && categoryModal) {
      closeCategoryBtn.addEventListener("click", () => {
        categoryModal.classList.remove("active");
      });

      categoryModal.addEventListener("click", (e) => {
        if (e.target === categoryModal) {
          categoryModal.classList.remove("active");
        }
      });
    }
  }
});

let usersPage = 1;
let usersLimit = 25;
let usersTotal = 0;
let users = [];

// Load users from server (paginated/searchable)
function loadAdminUsers(page = 1, q = "") {
  usersPage = page || 1;
  const params = new URLSearchParams();
  params.set("page", usersPage);
  params.set("limit", usersLimit);
  if (q) params.set("q", q);

  fetch("../api/admin/users.php?" + params.toString(), {
    credentials: "same-origin",
  })
    .then((r) => r.json())
    .then((res) => {
      if (!res || !res.data) {
        console.error("Failed to load users", res);
        return;
      }
      usersTotal = res.total || 0;
      users = (res.data || []).map((u) => ({
        id: String(u.id),
        first_name: u.first_name || "",
        last_name: u.last_name || "",
        name:
          ((u.first_name || "") + " " + (u.last_name || "")).trim() ||
          u.email ||
          "",
        email: u.email || "",
        phone: u.phone || "",
        role: u.role || "Customer",
        is_current: !!u.is_current,
        joinDate: u.created_at || "",
      }));
      renderUsers(document.getElementById("userSearch")?.value || "");
      renderUsersPagination();
    })
    .catch((err) => console.error("Failed to load users", err));
}

// Activities are now loaded from the database via API

function formatCurrency(amount) {
  return "TZS " + amount.toLocaleString();
}

function generateId() {
  return Date.now().toString();
}

function getInitials(name) {
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
}

function getGreeting() {
  const hour = new Date().getHours();
  if (hour < 12) return "Good Morning";
  if (hour < 17) return "Good Afternoon";
  return "Good Evening";
}

function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  const toastMessage = document.getElementById("toastMessage");
  const toastIcon = document.getElementById("toastIcon");

  toastMessage.textContent = message;

  // Remove previous type classes
  toast.classList.remove("toast-success", "toast-error");

  // Add appropriate type class
  if (type === "error") {
    toast.classList.add("toast-error");
    // Update icon for error
    toastIcon.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="15" y1="9" x2="9" y2="15"></line>
        <line x1="9" y1="9" x2="15" y2="15"></line>
      </svg>
    `;
  } else {
    toast.classList.add("toast-success");
    // Update icon for success
    toastIcon.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
    `;
  }

  toast.classList.add("show");

  // Auto-hide after 4 seconds
  const hideTimeout = setTimeout(() => {
    toast.classList.remove("show");
  }, 4000);

  // Store timeout ID so we can clear it if user closes manually
  toast.dataset.hideTimeout = hideTimeout;
}

// Add close button handler
document.addEventListener("DOMContentLoaded", () => {
  const toastClose = document.getElementById("toastClose");
  if (toastClose) {
    toastClose.addEventListener("click", () => {
      const toast = document.getElementById("toast");
      // Clear auto-hide timeout
      if (toast.dataset.hideTimeout) {
        clearTimeout(parseInt(toast.dataset.hideTimeout));
      }
      toast.classList.remove("show");
    });
  }
});

const sidebar = document.getElementById("sidebar");
const mainWrapper = document.getElementById("mainWrapper");
const overlay = document.getElementById("overlay");
const hamburgerBtn = document.getElementById("hamburgerBtn");
const pageTitle = document.getElementById("pageTitle");
const navItems = document.querySelectorAll(".nav-item[data-page]");

let sidebarCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

function updateSidebarState() {
  if (window.innerWidth >= 1024) {
    sidebar.classList.toggle("collapsed", sidebarCollapsed);
    mainWrapper.classList.toggle("sidebar-collapsed", sidebarCollapsed);
  }
}

hamburgerBtn.addEventListener("click", () => {
  if (window.innerWidth < 1024) {
    sidebar.classList.toggle("mobile-open");
    overlay.classList.toggle("active");
  } else {
    sidebarCollapsed = !sidebarCollapsed;
    localStorage.setItem("sidebarCollapsed", sidebarCollapsed);
    updateSidebarState();
  }
});

overlay.addEventListener("click", () => {
  sidebar.classList.remove("mobile-open");
  overlay.classList.remove("active");
});

function closeMobileSidebar() {
  sidebar.classList.remove("mobile-open");
  overlay.classList.remove("active");
}

const pageTitles = {
  dashboard: "Dashboard",
  categories: "Menu Categories",
  "food-items": "Food Items",
  orders: "Orders",
  users: "Users Management",
  settings: "Settings",
  gallery: "Gallery Management",
  offers: "Special Offers",
};

function showPage(pageId) {
  document
    .querySelectorAll(".page")
    .forEach((p) => p.classList.remove("active"));
  
  const targetPage = document.getElementById("page-" + pageId);
  if (targetPage) {
    targetPage.classList.add("active");
  }

  navItems.forEach((item) => {
    item.classList.toggle("active", item.dataset.page === pageId);
  });

  if (pageId === "offers") {
    loadOffers();
  } else if (pageId === "gallery") {
    loadGallery();
  } else if (pageId !== "gallery") {
    // Auto-close Posts submenu when navigating to other main pages
    const postsGroup = document.getElementById("postsGroup");
    if (postsGroup) {
      postsGroup.classList.remove("expanded");
    }
  }

  pageTitle.textContent = pageTitles[pageId] || "Dashboard";
  closeMobileSidebar();
}

document.querySelectorAll(".nav-item[data-page]").forEach((item) => {
  item.addEventListener("click", (e) => {
    e.preventDefault();
    showPage(item.dataset.page);
  });
});

// Posts Submenu Toggle
const postsToggle = document.getElementById("postsToggle");
const postsGroup = document.getElementById("postsGroup");
if (postsToggle && postsGroup) {
  postsToggle.addEventListener("click", (e) => {
    e.preventDefault();
    postsGroup.classList.toggle("expanded");
  });
}

// Theme initialization is handled at the bottom of the file

function openModal(modalId) {
  document.getElementById(modalId).classList.add("active");
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove("active");
}

document.querySelectorAll("[data-close-modal]").forEach((btn) => {
  btn.addEventListener("click", () => {
    btn.closest(".modal").classList.remove("active");
  });
});

document.querySelectorAll(".modal").forEach((modal) => {
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.classList.remove("active");
  });
});

let deleteCallback = null;

function confirmDelete(title, message, callback) {
  document.getElementById("deleteModalTitle").textContent = title;
  document.getElementById("deleteModalMessage").textContent = message;
  deleteCallback = callback;
  openModal("deleteModal");
}

document.getElementById("confirmDeleteBtn").addEventListener("click", () => {
  if (deleteCallback) {
    deleteCallback();
    deleteCallback = null;
  }
  closeModal("deleteModal");
});

function renderDashboard() {
  const serverNameEl = document.querySelector(".user-name");
  const name = serverNameEl ? serverNameEl.textContent.trim() : "Admin";
  const welcomeEl = document.querySelector(".welcome-title");
  if (welcomeEl) welcomeEl.textContent = `Hi, ${name}`;

  // fetch real activities from server
  const tbody = document.getElementById("activityTableBody");
  if (!tbody) return;

  fetch("../api/admin/activities/list.php", { credentials: "same-origin" })
    .then((r) => r.json())
    .then((res) => {
      if (!res.success) {
        tbody.innerHTML =
          '<tr><td colspan="3">Failed to load activities</td></tr>';
        const clearBtn = document.getElementById("clearRecentsBtn");
        if (clearBtn) clearBtn.style.display = "none";
        return;
      }
      
      const lastClearedId = parseInt(localStorage.getItem('lastClearedActivityId') || '0');
      const filteredData = (res.data || []).filter(a => parseInt(a.id) > lastClearedId);

      if (filteredData.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="3" class="text-center">No recent activity</td></tr>';
        const clearBtn = document.getElementById("clearRecentsBtn");
        if (clearBtn) clearBtn.style.display = "none";
        return;
      }

      const clearBtn = document.getElementById("clearRecentsBtn");
      if (clearBtn) clearBtn.style.display = "flex";

      tbody.innerHTML = filteredData
        .map((a) => {
          // Time formatting: "full date with day"
          // Eg: Wednesday, 18 February 2026 03:54
          const date = new Date(a.time);
          const timeStr = date.toLocaleDateString("en-GB", {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });

          // Formatting Activity: "[Action]: [UserSuffix]"
          const fullActivity = `${a.action}: ${a.user_suffix}`;

          return `
          <tr class="activity-row">
            <td>
              <div style="display: flex; flex-direction: column;">
                <span>${fullActivity}</span>
              </div>
            </td>
            <td><span class="badge badge-${a.type}">${a.type.charAt(0).toUpperCase() + a.type.slice(1)}</span></td>
            <td>${timeStr}</td>
          </tr>
        `;
        })
        .join("");
    })
    .catch(() => {
      tbody.innerHTML =
        '<tr><td colspan="3">Failed to load activities</td></tr>';
    });
}

let editingCategoryId = null;

function renderCategories(filter = "") {
  const grid = document.getElementById("categoriesGrid");
  const filtered = categories.filter(
    (c) =>
      c.name.toLowerCase().includes(filter.toLowerCase()) ||
      String(c.itemCount).includes(filter),
  );

  grid.innerHTML = filtered
    .map(
      (c) => `
     <div class="category-card" data-id="${c.id}" onclick="showCategoryFoods('${c.id}', '${c.name.replace(/'/g, "\\'")}')">
       <div class="category-info">
         <h4>${c.name}</h4>
         <p>${c.itemCount} items</p>
       </div>
       <div class="category-actions">
         <button class="btn-icon edit" onclick="event.stopPropagation(); editCategory('${c.id}')">
           <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
         </button>
         <button class="btn-icon delete" onclick="event.stopPropagation(); deleteCategory('${c.id}')">
           <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
         </button>
       </div>
     </div>
   `,
    )
    .join("");

  if (filtered.length === 0) {
    grid.innerHTML = '<div class="empty-state">No categories found</div>';
  }
}

function showCategoryFoods(id, name) {
  const modal = document.getElementById("categoryItemsModal");
  const title = document.getElementById("categoryModalTitle");
  const list = document.getElementById("categoryItemsList");
  const empty = document.getElementById("categoryItemsEmpty");
  const loading = document.getElementById("categoryItemsLoading");

  if (!modal) return;

  title.textContent = `Items in ${name}`;
  list.innerHTML = "";
  empty.style.display = "none";
  loading.style.display = "flex";
  modal.classList.add("active");

  fetch(`../api/food/list.php?category_id=${id}`, { credentials: "same-origin" })
    .then((r) => r.json())
    .then((res) => {
      loading.style.display = "none";
      if (!res.success || !res.data || res.data.length === 0) {
        empty.style.display = "block";
        return;
      }

      list.innerHTML = res.data
        .map(
          (item) => `
        <div class="food-item-mini">
          <img src="../${item.image_path}" alt="${item.food_name}" class="food-item-mini-img" onerror="this.src='../assets/images/food-6.jpg'">
          <div class="food-item-mini-info">
            <div class="food-item-mini-name">${item.food_name}</div>
            <div class="food-item-mini-price">${formatCurrency(item.price)}</div>
            <div class="food-item-mini-status">
              <span class="badge badge-${item.availability_status === 'Available' ? 'success' : 'danger'}">
                ${item.availability_status}
              </span>
            </div>
          </div>
        </div>
      `,
        )
        .join("");
    })
    .catch(() => {
      loading.style.display = "none";
      empty.textContent = "Error loading items.";
      empty.style.display = "block";
    });
}

document.getElementById("categorySearch").addEventListener("input", (e) => {
  renderCategories(e.target.value);
});

document.getElementById("addCategoryBtn").addEventListener("click", () => {
  editingCategoryId = null;
  document.getElementById("categoryModalTitle").textContent = "Add Category";
  document.getElementById("categoryNameInput").value = "";
  clearCategoryFormErrors();
  openModal("categoryModal");
});

window.editCategory = function (id) {
  const cat = categories.find((c) => c.id === id);
  if (!cat) return;
  editingCategoryId = id;
  document.getElementById("categoryModalTitle").textContent = "Edit Category";
  document.getElementById("categoryNameInput").value = cat.name;
  openModal("categoryModal");
};

window.deleteCategory = function (id) {
  confirmDelete(
    "Delete Category",
    "Are you sure you want to delete this category?",
    () => {
      fetch("../api/admin/category/delete.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
      })
        .then((r) => r.json())
        .then((res) => {
          if (res && res.success) {
            loadCategories();
            showToast("Category deleted successfully", "success");
          } else {
            showToast(
              res && res.message ? res.message : "Delete failed",
              "error",
            );
          }
        })
        .catch((err) => {
          console.error(err);
          showToast("Delete failed", "error");
        });
    },
  );
};

document.getElementById("saveCategoryBtn").addEventListener("click", () => {
  const name = document.getElementById("categoryNameInput").value.trim();
  if (!name) return;
  const payload = { name };
  if (editingCategoryId) payload.id = editingCategoryId;

  const url = editingCategoryId
    ? "../api/admin/category/update.php"
    : "../api/admin/category/create.php";
  fetch(url, {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res && res.success) {
        loadCategories();
        closeModal("categoryModal");
        showToast(
          editingCategoryId ? "Category updated" : "Category added",
          "success",
        );
      } else if (res && res.errors) {
        showCategoryFormErrors(res.errors);
      } else {
        showToast(res && res.message ? res.message : "Save failed", "error");
      }
    })
    .catch((err) => {
      console.error(err);
      showToast("Save failed", "error");
    });
});

let editingFoodId = null;

function renderFoodItems(filter = "") {
  const grid = document.getElementById("foodGrid");
  const filtered = foodItems.filter(
    (f) =>
      f.name.toLowerCase().includes(filter.toLowerCase()) ||
      f.category.toLowerCase().includes(filter.toLowerCase()) ||
      f.description.toLowerCase().includes(filter.toLowerCase()) ||
      String(f.price).includes(filter),
  );

  grid.innerHTML = filtered
    .map(
      (f) => `
     <div class="food-card" data-id="${f.id}">
       <div class="food-image">
         <img src="../${f.image}" alt="${f.name}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.innerHTML='<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\' ry=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg>'">
       </div>
       <div class="food-content">
         <div class="food-header">
           <span class="food-name">${f.name}</span>
           <span class="food-price">${formatCurrency(f.price)}</span>
         </div>
         <p class="food-category">${f.category}</p>
         <p class="food-desc">${f.description}</p>
         <div class="food-footer">
           <div class="availability-toggle">
             <label class="switch">
               <input type="checkbox" ${f.available ? "checked" : ""} onchange="toggleFoodAvailability('${f.id}')">
               <span class="slider"></span>
             </label>
             <span>${f.available ? "Available" : "Unavailable"}</span>
           </div>
           <div class="food-actions">
             <button class="btn-icon edit" onclick="editFood('${f.id}')">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
             </button>
             <button class="btn-icon delete" onclick="deleteFood('${f.id}')">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
             </button>
           </div>
         </div>
       </div>
     </div>
   `,
    )
    .join("");

  if (filtered.length === 0) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:4rem 2rem;color:hsl(var(--muted-foreground));opacity:0.7;font-size:1.1rem;width:100%;">No food items found</div>';
  }
}

function updateFoodCategorySelect() {
  const select = document.getElementById("foodCategoryInput");
  select.innerHTML =
    '<option value="">Select category</option>' +
    categories
      .map((c) => `<option value="${c.id}">${c.name}</option>`)
      .join("");
}

document.getElementById("foodSearch").addEventListener("input", (e) => {
  loadFoodItems(e.target.value);
});

document.getElementById("addFoodBtn").addEventListener("click", () => {
  editingFoodId = null;
  document.getElementById("foodModalTitle").textContent = "Add Food Item";
  document.getElementById("foodNameInput").value = "";
  document.getElementById("foodCategoryInput").value = "";
  document.getElementById("foodPriceInput").value = "";
  document.getElementById("foodDescInput").value = "";

  // Reset preview
  const preview = document.getElementById("foodImagePreview");
  const placeholder = document.getElementById("uploadPlaceholder");
  if (preview) {
    preview.src = "";
    preview.style.display = "none";
  }
  if (placeholder) placeholder.style.display = "block";

  updateFoodCategorySelect();
  clearFoodFormErrors();
  openModal("foodModal");
});

window.editFood = function (id) {
  const food = foodItems.find((f) => f.id === id);
  if (!food) return;
  editingFoodId = id;
  document.getElementById("foodModalTitle").textContent = "Edit Food Item";
  document.getElementById("foodNameInput").value = food.name;
  document.getElementById("foodPriceInput").value = food.price;
  document.getElementById("foodDescInput").value = food.description;

  // Show existing image in preview
  const preview = document.getElementById("foodImagePreview");
  const placeholder = document.getElementById("uploadPlaceholder");
  if (preview && food.image) {
    preview.src = `../${food.image}`;
    preview.style.display = "block";
    if (placeholder) placeholder.style.display = "none";
  } else {
    if (preview) {
      preview.src = "";
      preview.style.display = "none";
    }
    if (placeholder) placeholder.style.display = "block";
  }

  updateFoodCategorySelect();
  document.getElementById("foodCategoryInput").value = food.category_id || "";
  openModal("foodModal");
};

window.deleteFood = function (id) {
  confirmDelete(
    "Delete Food Item",
    "Are you sure you want to delete this food item?",
    () => {
      fetch("../api/admin/food/delete.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
      })
        .then((r) => r.json())
        .then((res) => {
          if (res && res.success) {
            loadFoodItems();
            showToast("Food item deleted successfully", "success");
          } else {
            showToast(
              res && res.message ? res.message : "Delete failed",
              "error",
            );
          }
        })
        .catch((err) => {
          console.error(err);
          showToast("Delete failed", "error");
        });
    },
  );
};

window.toggleFoodAvailability = function (id) {
  const food = foodItems.find((f) => f.id === id);
  if (!food) return;
  const newState = !food.available;
  fetch("../api/admin/food/toggle.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, available: newState ? 1 : 0 }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res && res.success) {
        loadFoodItems();
        showToast("Availability updated", "success");
      } else {
        showToast(res && res.message ? res.message : "Update failed", "error");
      }
    })
    .catch((err) => {
      console.error(err);
      showToast("Update failed", "error");
    });
};

document.getElementById("saveFoodBtn").addEventListener("click", () => {
  const name = document.getElementById("foodNameInput").value.trim();
  const categoryId = document.getElementById("foodCategoryInput").value;
  const price =
    parseFloat(document.getElementById("foodPriceInput").value) || 0;
  const description = document.getElementById("foodDescInput").value.trim();
  const imageFile = document.getElementById("foodImageInput").files[0];

  if (!name || !categoryId || !price) return;

  // Helper to send create/update request
  async function sendPayload(imageName) {
    const payload = {
      name,
      category_id: Number(categoryId),
      price,
      description,
      image: imageName || null,
    };
    if (editingFoodId) payload.id = editingFoodId;

    const url = editingFoodId
      ? "../api/admin/food/update.php"
      : "../api/admin/food/create.php";
    try {
      const res = await fetch(url, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const body = await res.json();
      if (body && body.success) {
        loadFoodItems();
        closeModal("foodModal");
        showToast(
          editingFoodId ? "Food item updated" : "Food item added",
          "success",
        );
      } else {
        if (body && body.errors) {
          showFoodFormErrors(body.errors);
        } else {
          showToast(
            body && body.message ? body.message : "Save failed",
            "error",
          );
        }
      }
    } catch (err) {
      console.error(err);
      showToast("Save failed", "error");
    }
  }

  // If there's an image file selected, upload it first
  if (imageFile) {
    const fd = new FormData();
    fd.append("image", imageFile);
    fetch("../api/admin/food/upload.php", {
      method: "POST",
      credentials: "same-origin",
      body: fd,
    })
      .then((r) => r.json())
      .then((j) => {
        if (j && j.success) {
          sendPayload(j.filename);
        } else {
          showToast(j && j.message ? j.message : "Image upload failed");
        }
      })
      .catch((err) => {
        console.error(err);
        showToast("Image upload failed");
      });
  } else {
    sendPayload(null);
  }
});

document.getElementById("foodImageInput").addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (event) {
      const preview = document.getElementById("foodImagePreview");
      const placeholder = document.getElementById("uploadPlaceholder");
      if (preview) {
        preview.src = event.target.result;
        preview.style.display = "block";
      }
      if (placeholder) {
        placeholder.style.display = "none";
      }
    };
    reader.readAsDataURL(file);
  }
});

document.getElementById("uploadZone").addEventListener("click", () => {
  document.getElementById("foodImageInput").click();
});

function renderOrders(filter = "", status = "all") {
  const tbody = document.getElementById("ordersTableBody");
  let filtered = orders.filter(
    (o) =>
      String(o.id).toLowerCase().includes(filter.toLowerCase()) ||
      (o.customer && o.customer.toLowerCase().includes(filter.toLowerCase())) ||
      (o.phone && o.phone.toLowerCase().includes(filter.toLowerCase())) ||
      (o.date && o.date.toLowerCase().includes(filter.toLowerCase())) ||
      (o.created_at &&
        o.created_at.toLowerCase().includes(filter.toLowerCase())) ||
      (Array.isArray(o.items) ? o.items.join(", ") : o.items)
        .toLowerCase()
        .includes(filter.toLowerCase()) ||
      String(o.total).includes(filter),
  );

  if (status !== "all") {
    filtered = filtered.filter((o) => o.status === status);
  }

  tbody.innerHTML = filtered
    .map(
      (o, index) => `
     <tr>
        <td>${index + 1}</td>
        <td>${o.customer}</td>
        <td>${Array.isArray(o.items) ? o.items.join(", ") : o.items}</td>
       <td>${formatCurrency(o.total)}</td>
       <td><span class="badge badge-${o.status}">${o.status}</span></td>
       <td>
         <div class="actions-group">
           <button class="btn-icon view" onclick="viewOrder('${o.id}')">
             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
           </button>
           ${
             o.status !== "completed" && o.status !== "cancelled"
               ? `
             <button class="btn-icon check" onclick="completeOrder('${o.id}')" title="Complete Order">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-8.93"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
             </button>
             <button class="btn-icon delete" onclick="cancelOrder('${o.id}')" title="Cancel Order">
               <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
             </button>
           `
               : ""
           }
           <button class="btn-icon delete" onclick="deleteOrder('${o.id}')" title="Delete Order">
             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
           </button>
         </div>
       </td>
     </tr>
   `,
    )
    .join("");

  if (filtered.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="6" style="text-align:center;padding:4rem 2rem;color:hsl(var(--muted-foreground));opacity:0.7;font-size:1.1rem;">No orders found</td></tr>';
  }
}

document.getElementById("orderSearch").addEventListener("input", (e) => {
  // Only search locally for now, or trigger reload (hybrid approach)
  renderOrders(
    e.target.value,
    document.getElementById("orderStatusFilter").value,
  );
});

document.getElementById("orderStatusFilter").addEventListener("change", (e) => {
  renderOrders(document.getElementById("orderSearch").value, e.target.value);
});

window.viewOrder = function (id) {
  // Ensure we compare strings to avoid type mismatches
  const order = orders.find((o) => String(o.id) === String(id));
  if (!order) {
    console.error("Order not found:", id);
    showToast("Error: Order not found");
    return;
  }

  const modalBody = document.getElementById("orderModalBody");
  if (!modalBody) {
    console.error("orderModalBody not found");
    return;
  }

  modalBody.innerHTML = `
      <div class="order-detail"><label>ORDER ID</label><p>#${order.id}</p></div>
      <div class="order-detail"><label>CUSTOMER NAME</label><p>${order.customer}</p></div>
      <div class="order-detail"><label>PHONE</label><p>${order.phone}</p></div>
      <div class="order-detail"><label>ITEMS</label><p>${Array.isArray(order.items) ? order.items.join(", ") : order.items}</p></div>
     <div class="order-detail"><label>TOTAL PRICE</label><p>${formatCurrency(order.total)}</p></div>
     <div class="order-detail"><label>ORDER DATE</label><p>${order.date || order.created_at}</p></div>
     <div class="order-detail"><label>STATUS</label><p><span class="badge badge-${order.status}">${order.status}</span></p></div>
   `;

  // Check if openModal exists, otherwise fallback to class manipulation
  if (typeof openModal === "function") {
    openModal("orderModal");
  } else {
    const modal = document.getElementById("orderModal");
    if (modal) modal.classList.add("active");
  }
};

window.approveOrder = function (id) {
  const order = orders.find((o) => o.id == id); // loose comparison for string/int IDs
  if (order) {
    updateOrderStatus(id, "approved");
  }
};

window.completeOrder = function (id) {
  updateOrderStatus(id, "completed");
};

function updateOrderStatus(id, status) {
  fetch("../api/admin/orders/update_status.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, status }),
  })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        showToast(`Order ${status}`);
        loadOrders(); // Reload to refresh list
      } else {
        showToast(res.message || "Update failed");
      }
    })
    .catch((err) => {
      console.error(err);
      showToast("Update failed");
    });
}

window.cancelOrder = function (id) {
  confirmDelete(
    "Cancel Order",
    "Are you sure you want to cancel this order?",
    () => {
      updateOrderStatus(id, "cancelled");
    },
  );
};

window.deleteOrder = function (id) {
  confirmDelete(
    "Delete Order",
    "Are you sure you want to delete this order?",
    () => {
      fetch("../api/admin/orders/delete.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            showToast("Order deleted successfully");
            loadOrders();
          } else {
            showToast(res.message || "Delete failed");
          }
        })
        .catch((err) => {
          console.error(err);
          showToast("Delete failed");
        });
    },
  );
};

let editingUserId = null;

function renderUsers(filter = "") {
  const tbody = document.getElementById("usersTableBody");
  const q = (filter || "").toLowerCase().trim();
  const filteredUsers = q
    ? users.filter(
        (u) =>
          (u.name || "").toLowerCase().includes(q) ||
          (u.email || "").toLowerCase().includes(q) ||
          (u.role || "").toLowerCase().includes(q) ||
          (u.joinDate || "").toLowerCase().includes(q),
      )
    : users;
  tbody.innerHTML = filteredUsers
    .map(
      (u) => `
     <tr>
       <td>
         <div class="user-row-info">
           <div class="avatar">${getInitials(u.name)}</div>
           <span>${u.name}</span>
         </div>
       </td>
       <td>${u.email}</td>
      <td><span class="badge badge-${u.role.toLowerCase()}">${u.role}</span></td>
       <td>
         <div class="actions-group">
           <button class="btn-icon view" onclick="viewUser('${u.id}')" title="View Details">
             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
           </button>
           <button class="btn-icon edit" onclick="editUser('${u.id}')" title="Edit User">
             <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
           </button>
          ${
            u.is_current
              ? '<button class="btn-icon delete disabled" title="Cannot delete the currently logged-in user">\n            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>\n          </button>'
              : `<button class="btn-icon delete" onclick="deleteUser('${u.id}')">\n            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>\n          </button>`
          }
         </div>
       </td>
     </tr>
   `,
    )
    .join("");

  if (filteredUsers.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="4" style="text-align:center;padding:4rem 2rem;color:hsl(var(--muted-foreground));opacity:0.7;font-size:1.1rem;">No users found</td></tr>';
  }
}

function viewUser(id) {
  const user = users.find((u) => u.id === id);
  if (!user) return;

  const modalBody = document.getElementById("userDetailsBody");
  if (!modalBody) return;

  modalBody.innerHTML = `
    <div class="order-detail"><label>FULL NAME</label><p>${user.name}</p></div>
    <div class="order-detail"><label>EMAIL ADDRESS</label><p>${user.email}</p></div>
    <div class="order-detail"><label>PHONE NUMBER</label><p>${user.phone || "N/A"}</p></div>
    <div class="order-detail"><label>ROLE</label><p><span class="badge badge-${user.role.toLowerCase()}">${user.role}</span></p></div>
    <div class="order-detail"><label>JOIN DATE</label><p>${user.joinDate || "N/A"}</p></div>
  `;

  // Set up edit button in modal
  const editBtn = document.getElementById("editUserFromDetailsBtn");
  if (editBtn) {
    editBtn.onclick = () => {
      closeModal("userDetailsModal");
      editUser(id);
    };
  }

  openModal("userDetailsModal");
}

// Pagination for server-backed users removed — using local mock users.
function renderUsersPagination() {
  const el = document.getElementById("usersPaginationInfo");
  if (el) {
    const from = (usersPage - 1) * usersLimit + 1;
    const to = Math.min(usersPage * usersLimit, usersTotal || users.length);
    el.textContent = usersTotal ? `${from}-${to} of ${usersTotal}` : "";
  }
}

function showUserFormErrors(errors) {
  let container = document.getElementById("userFormErrors");
  if (!container) {
    const modal = document.getElementById("userModal");
    if (!modal) return;
    container = document.createElement("div");
    container.id = "userFormErrors";
    container.className = "form-errors";
    modal
      .querySelector(".modal-body")
      ?.insertBefore(
        container,
        modal.querySelector(".modal-body").firstChild || null,
      );
  }
  container.innerHTML = "";
  const keys = Object.keys(errors || {});
  if (keys.length === 0) return;
  const ul = document.createElement("ul");
  keys.forEach((k) => {
    const li = document.createElement("li");
    li.textContent = errors[k];
    ul.appendChild(li);
  });
  container.appendChild(ul);

  // Auto-dismiss after 3 seconds
  setTimeout(() => {
    if (container && container.parentNode) {
      container.style.opacity = "0";
      container.style.transition = "opacity 0.3s ease";
      setTimeout(() => {
        container.remove();
      }, 300);
    }
  }, 3000);
}

function clearUserFormErrors() {
  const container = document.getElementById("userFormErrors");
  if (container) container.innerHTML = "";
}

function showCategoryFormErrors(errors) {
  let container = document.getElementById("categoryFormErrors");
  if (!container) {
    const modal = document.getElementById("categoryModal");
    if (!modal) return;
    container = document.createElement("div");
    container.id = "categoryFormErrors";
    container.className = "form-errors";
    modal
      .querySelector(".modal-body")
      ?.insertBefore(
        container,
        modal.querySelector(".modal-body").firstChild || null,
      );
  }
  container.innerHTML = "";
  const keys = Object.keys(errors || {});
  if (keys.length === 0) return;
  const ul = document.createElement("ul");
  keys.forEach((k) => {
    const li = document.createElement("li");
    li.textContent = errors[k];
    ul.appendChild(li);
  });
  container.appendChild(ul);

  // Auto-dismiss after 3 seconds
  setTimeout(() => {
    if (container && container.parentNode) {
      container.style.opacity = "0";
      container.style.transition = "opacity 0.3s ease";
      setTimeout(() => {
        container.remove();
      }, 300);
    }
  }, 3000);
}

function clearCategoryFormErrors() {
  const container = document.getElementById("categoryFormErrors");
  if (container) container.innerHTML = "";
}

function showFoodFormErrors(errors) {
  let container = document.getElementById("foodFormErrors");
  if (!container) {
    const modal = document.getElementById("foodModal");
    if (!modal) return;
    container = document.createElement("div");
    container.id = "foodFormErrors";
    container.className = "form-errors";
    modal
      .querySelector(".modal-body")
      ?.insertBefore(
        container,
        modal.querySelector(".modal-body").firstChild || null,
      );
  }
  container.innerHTML = "";
  const keys = Object.keys(errors || {});
  if (keys.length === 0) return;
  const ul = document.createElement("ul");
  keys.forEach((k) => {
    const li = document.createElement("li");
    li.textContent = errors[k];
    ul.appendChild(li);
  });
  container.appendChild(ul);

  // Auto-dismiss after 3 seconds
  setTimeout(() => {
    if (container && container.parentNode) {
      container.style.opacity = "0";
      container.style.transition = "opacity 0.3s ease";
      setTimeout(() => {
        container.remove();
      }, 300);
    }
  }, 3000);
}

function clearFoodFormErrors() {
  const container = document.getElementById("foodFormErrors");
  if (container) container.innerHTML = "";
}

// POST JSON helper: returns { ok, status, body }
async function apiPost(path, payload) {
  try {
    const res = await fetch(path, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    const text = await res.text();
    let body = null;
    try {
      body = text ? JSON.parse(text) : null;
    } catch (e) {
      body = null;
    }
    return { ok: res.ok, status: res.status, body };
  } catch (err) {
    console.error("apiPost error", err);
    return { ok: false, status: 0, body: null, error: err };
  }
}

document.getElementById("userSearch").addEventListener("input", (e) => {
  const q = e.target.value.trim();
  loadAdminUsers(1, q);
});

function ensureUserPasswordToggle() {
  const input = document.getElementById("userPasswordInput");
  if (!input) return;
  input.type = "password";
  const btn = input.parentElement.querySelector(".password-toggle");
  if (btn) {
    const svg = btn.querySelector("svg");
    if (svg) {
      svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
  }
}

document.getElementById("addUserBtn").addEventListener("click", () => {
  editingUserId = null;
  document.getElementById("userModalTitle").textContent = "Add User";
  document.getElementById("userNameInput").value = "";
  document.getElementById("userEmailInput").value = "";
  document.getElementById("userPhoneInput").value = "";
  document.getElementById("userPasswordInput").value = "";
  document.getElementById("userRoleInput").value = "Customer";
  document.getElementById("userPasswordGroup").style.display = "block";
  // ensure password toggle exists and is set to visible by default for add
  ensureUserPasswordToggle(true);
  clearUserFormErrors();
  openModal("userModal");
});

window.editUser = function (id) {
  const user = users.find((u) => u.id === id);
  if (!user) return;
  editingUserId = id;
  document.getElementById("userModalTitle").textContent = "Edit User";
  document.getElementById("userNameInput").value = user.name;
  document.getElementById("userEmailInput").value = user.email;
  document.getElementById("userPhoneInput").value = user.phone || "";
  document.getElementById("userPasswordInput").value = "";
  document.getElementById("userRoleInput").value = user.role;
  document.getElementById("userPasswordGroup").style.display = "none";
  // disable role change if editing current user
  if (user.is_current) {
    document.getElementById("userRoleInput").disabled = true;
  } else {
    document.getElementById("userRoleInput").disabled = false;
  }
  ensureUserPasswordToggle(false);
  clearUserFormErrors();
  openModal("userModal");
};

window.deleteUser = function (id) {
  confirmDelete(
    "Delete User",
    "Are you sure you want to delete this user?",
    () => {
      apiPost("../api/admin/user/delete.php", { id: id })
        .then((result) => {
          if (result.ok && result.body && result.body.success) {
            showToast("User deleted successfully");
            loadAdminUsers(
              usersPage,
              document.getElementById("userSearch")?.value || "",
            );
          } else if (result.body && result.body.errors) {
            showUserFormErrors(result.body.errors);
          } else if (result.body && result.body.message) {
            showToast(result.body.message);
          } else {
            showToast("Delete failed");
          }
        })
        .catch((err) => {
          console.error(err);
          showToast("Delete failed");
        });
    },
  );
};

document.getElementById("saveUserBtn").addEventListener("click", () => {
  clearUserFormErrors();
  const name = document.getElementById("userNameInput").value.trim();
  const email = document.getElementById("userEmailInput").value.trim();
  const phone = document.getElementById("userPhoneInput").value.trim();
  const password = document.getElementById("userPasswordInput").value.trim();
  const role = document.getElementById("userRoleInput").value;

  // Validation
  const errors = {};

  if (!name) {
    errors.name = "Full name is required";
  } else if (name.length < 3) {
    errors.name = "Name must be at least 3 characters";
  } else if (!/^[a-zA-Z\s]+$/.test(name)) {
    errors.name = "Name should only contain letters and spaces";
  }

  if (!email) {
    errors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    errors.email = "Please enter a valid email address";
  }

  if (!editingUserId) {
    // For new users, password is required
    if (!password) {
      errors.password = "Password is required";
    } else if (password.length < 8) {
      errors.password = "Password must be at least 8 characters";
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
      errors.password =
        "Password must contain uppercase, lowercase, and number";
    }
  } else if (password && password.length < 8) {
    // For editing, if password is provided, validate it
    errors.password = "Password must be at least 8 characters";
  }

  if (Object.keys(errors).length > 0) {
    showUserFormErrors(errors);
    return;
  }

  const parts = name.split(" ").filter(Boolean);
  const first_name = parts.shift() || "";
  const last_name = parts.join(" ") || "";

  if (editingUserId) {
    const payload = { id: editingUserId, first_name, last_name, email, phone, role };
    if (password) payload.password = password;
    apiPost("../api/admin/user/update.php", payload)
      .then((result) => {
        if (result.ok && result.body && result.body.success) {
          closeModal("userModal");
          loadAdminUsers(
            usersPage,
            document.getElementById("userSearch")?.value || "",
          );
          showToast("User updated successfully");
        } else if (result.body && result.body.errors) {
          showUserFormErrors(result.body.errors);
        } else if (result.body && result.body.message) {
          showToast(result.body.message, "error");
        } else {
          showToast("Update failed", "error");
        }
      })
      .catch((err) => {
        console.error(err);
        showToast("Update failed", "error");
      });
  } else {
    const payload = { first_name, last_name, email, phone, password, role };
    apiPost("../api/admin/user/create.php", payload)
      .then((result) => {
        if (result.ok && result.body && result.body.success) {
          closeModal("userModal");
          loadAdminUsers(1);
          showToast("User added successfully");
        } else if (result.body && result.body.errors) {
          showUserFormErrors(result.body.errors);
        } else if (result.body && result.body.message) {
          showToast(result.body.message, "error");
        } else {
          showToast("Add failed", "error");
        }
      })
      .catch((err) => {
        console.error(err);
        showToast("Add failed", "error");
      });
  }
});


// Settings Tab Navigation
window.showTab = function (tabName) {
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.classList.toggle("active", tab.dataset.tab === tabName);
  });
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active");
  });
  document.getElementById(`tab-${tabName}`).classList.add("active");
};

// Profile Update Handler
window.handleProfileUpdate = async function (e) {
  e.preventDefault();
  const btn = document.getElementById("saveProfileBtn");
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
      // Update sidebar/header name immediately
      const userNameEl = document.querySelector(".user-name");
      if (userNameEl)
        userNameEl.textContent = firstName + (lastName ? " " + lastName : "");
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
};

// Password Change Handler
window.handlePasswordChange = async function (e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector('button[type="submit"]');
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
      form.reset();
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
};

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

// Theme Management
window.toggleTheme = function () {
  const isDark = document.getElementById("themeSwitch").checked;
  setTheme(isDark ? "dark" : "light");
};

window.setTheme = function (theme) {
  const isDark = theme === "dark";
  document.body.classList.toggle("dark", isDark);
  localStorage.setItem("marsla-theme", theme);

  const themeSwitch = document.getElementById("themeSwitch");
  const themeStatus = document.getElementById("themeStatus");
  const themeIcon = document.getElementById("themeIcon");

  if (themeSwitch) themeSwitch.checked = isDark;
  if (themeStatus)
    themeStatus.textContent = isDark
      ? "Currently using dark theme"
      : "Currently using light theme";

  if (themeIcon) {
    if (isDark) {
      themeIcon.innerHTML = '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>';
    } else {
      themeIcon.innerHTML =
        '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>';
    }
  }

  document.getElementById("lightPreview")?.classList.toggle("active", !isDark);
  document.getElementById("darkPreview")?.classList.toggle("active", isDark);
};

function init() {
  // Initialize theme first
  const savedTheme = localStorage.getItem("marsla-theme") || "light";
  setTheme(savedTheme);

  updateSidebarState();
  renderDashboard();
  loadCategories();
  loadFoodItems();
  renderOrders();
  loadAdminUsers();
}

window.addEventListener("resize", () => {
  if (window.innerWidth >= 1024) {
    closeMobileSidebar();
    updateSidebarState();
  }
});

init();

// User Dropdown Logic
document.addEventListener("DOMContentLoaded", () => {
  const userInfoTrigger = document.getElementById("userInfoDropdownTrigger");
  const userDropdown = document.getElementById("userDropdown");
  const myProfileLink = document.getElementById("myProfileLink");

  if (userInfoTrigger && userDropdown) {
    // Toggle dropdown on click
    userInfoTrigger.addEventListener("click", (e) => {
      // If clicking inside the dropdown, do not toggle (keep it open)
      // Note: Links with their own handlers will stop propagation, so they won't reach here anyway.
      if (userDropdown.contains(e.target)) return;

      e.stopPropagation();
      userDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!userInfoTrigger.contains(e.target)) {
        userDropdown.classList.remove("show");
      }
    });
  }

  // Handle "My Profile" click
  if (myProfileLink) {
    myProfileLink.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation(); // Stop propagation to prevent parent toggle from firing

      if (typeof showPage === "function") {
        showPage("settings");
      }
      
      if (typeof showTab === "function") {
        showTab("profile");
      } else {
        const profileTabBtn = document.querySelector('.tab[data-tab="profile"]');
        if (profileTabBtn) profileTabBtn.click();
      }

      if (userDropdown) userDropdown.classList.remove("show");
    });
  }

  // Offer Image Upload Preview
  const offerImageInput = document.getElementById("offerImageInput");
  const offerUploadZone = document.getElementById("offerUploadZone");
  const offerImagePreview = document.getElementById("offerImagePreview");

  if (offerUploadZone && offerImageInput) {
    offerUploadZone.addEventListener("click", () => offerImageInput.click());
    offerImageInput.addEventListener("change", (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
          offerImagePreview.querySelector("img").src = event.target.result;
          offerImagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });
  }
});

let offers = [];
let adminSliderInterval = null;
let adminCurrentSlide = 0;

async function loadOffers() {
  try {
    const response = await fetch("../api/admin/offers/list.php", { credentials: "same-origin" });
    const res = await response.json();
    if (res.success) {
      offers = res.data || [];
      renderOffers();
      renderOffersPreview();
    }
  } catch (err) {
    console.error("Failed to load offers", err);
  }
}

function renderOffers() {
  const tbody = document.getElementById("offersTableBody");
  if (!tbody) return;

  if (offers.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 4rem 2rem; color: var(--muted-foreground); font-size: 1.1rem; opacity: 0.7;">No offers found.</td></tr>';
    return;
  }

  tbody.innerHTML = offers.map(offer => `
    <tr>
      <td>
        <img src="../${offer.image_path}" alt="${offer.title}" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
      </td>
      <td><strong>${offer.title}</strong></td>
      <td>${offer.caption || '<span style="color:hsl(var(--muted-foreground));">No caption</span>'}</td>
      <td>${offer.display_order}</td>
      <td class="text-right">
        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
          <button class="btn-icon" onclick="showOfferModal(${offer.id})" title="Edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="btn-icon text-destructive" onclick="deleteOffer(${offer.id})" title="Delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </td>
    </tr>
  `).join("");
}

function renderOffersPreview() {
  const container = document.getElementById("offersPreviewContainer");
  if (!container) return;

  if (offers.length === 0) {
    container.innerHTML = '<div class="no-results">No offers to preview.</div>';
    if (adminSliderInterval) clearInterval(adminSliderInterval);
    return;
  }

  // Simplified version of the user dashboard slider
  container.innerHTML = `
    <div style="width: 100%; max-width: 800px; position: relative; overflow: hidden; border-radius: 1rem; box-shadow: var(--shadow-lg); background: hsl(var(--card));">
      <div id="adminSliderTrack" style="display: flex; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);">
        ${offers.map(offer => `
          <div class="admin-slide" style="min-width: 100%; position: relative; height: 300px;">
            <img src="../${offer.image_path}" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 60%); display: flex; flex-direction: column; justify-content: flex-end; padding: 1.5rem; color: white;">
              <h3 style="font-size: 1.5rem; margin: 0; font-weight: 600;">${offer.title}</h3>
              <p style="margin: 0.5rem 0 0; opacity: 0.9; font-size: 0.95rem;">${offer.caption}</p>
            </div>
          </div>
        `).join("")}
      </div>
      <div id="adminSliderDots" style="position: absolute; bottom: 1.5rem; left: 0; right: 0; display: flex; justify-content: center; gap: 0.6rem; z-index: 10;">
        ${offers.map((_, i) => `<div class="admin-dot" style="width: 8px; height: 8px; border-radius: 50%; background: ${i === 0 ? 'white' : 'rgba(255,255,255,0.4)'}; cursor: pointer; transition: all 0.3s ease;"></div>`).join("")}
      </div>
    </div>
  `;

  startAdminSlider();
}

function startAdminSlider() {
  if (adminSliderInterval) clearInterval(adminSliderInterval);
  adminCurrentSlide = 0;
  
  if (offers.length <= 1) return;

  adminSliderInterval = setInterval(() => {
    adminCurrentSlide = (adminCurrentSlide + 1) % offers.length;
    const track = document.getElementById("adminSliderTrack");
    const dots = document.querySelectorAll(".admin-dot");
    
    if (track) {
      track.style.transform = `translateX(-${adminCurrentSlide * 100}%)`;
    }
    
    dots.forEach((dot, i) => {
      dot.style.background = i === adminCurrentSlide ? "white" : "rgba(255,255,255,0.4)";
      dot.style.transform = i === adminCurrentSlide ? "scale(1.2)" : "scale(1)";
    });
  }, 4000);
}

window.showOfferModal = function(offerId = null) {
  const modal = document.getElementById("offerModal");
  const form = document.getElementById("offerForm");
  const title = document.getElementById("offerModalTitle");
  const preview = document.getElementById("offerImagePreview");

  form.reset();
  preview.style.display = "none";
  document.getElementById("offerIdInput").value = offerId || "";
  
  if (offerId) {
    title.textContent = "Edit Special Offer";
    const offer = offers.find(o => String(o.id) === String(offerId));
    if (offer) {
      document.getElementById("offerTitleInput").value = offer.title;
      document.getElementById("offerCaptionInput").value = offer.caption;
      document.getElementById("offerOrderInput").value = offer.display_order;
      preview.querySelector("img").src = "../" + offer.image_path;
      preview.style.display = "block";
    }
  } else {
    title.textContent = "Add Special Offer";
  }

  modal.classList.add("active");
};

window.handleOfferSubmit = async function() {
  const offerId = document.getElementById("offerIdInput").value;
  const title = document.getElementById("offerTitleInput").value;
  const caption = document.getElementById("offerCaptionInput").value;
  const order = document.getElementById("offerOrderInput").value;
  const imageFile = document.getElementById("offerImageInput").files[0];

  if (!title) {
    showToast("Please enter a title", "error");
    return;
  }

  try {
    let imagePath = null;

    if (imageFile) {
      const formData = new FormData();
      formData.append("image", imageFile);
      const uploadRes = await fetch("../api/admin/offers/upload.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin"
      });
      const uploadData = await uploadRes.json();
      if (uploadData.success) {
        imagePath = uploadData.path;
      } else {
        showToast("Image upload failed: " + uploadData.message, "error");
        return;
      }
    } else if (!offerId) {
      showToast("Please select an image", "error");
      return;
    }

    const payload = {
      id: offerId,
      title,
      caption,
      display_order: order,
    };
    if (imagePath) payload.image_path = imagePath;

    const endpoint = offerId ? "../api/admin/offers/update.php" : "../api/admin/offers/create.php";
    const response = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
      credentials: "same-origin"
    });

    const res = await response.json();
    if (res.success) {
      showToast(offerId ? "Offer updated" : "Offer created", "success");
      document.getElementById("offerModal").classList.remove("active");
      loadOffers();
    } else {
      showToast(res.message || "Failed to save offer", "error");
    }
  } catch (err) {
    console.error("Error saving offer:", err);
    showToast("An error occurred", "error");
  }
};

window.deleteOffer = function(offerId) {
  confirmDelete(
    "Delete Special Offer",
    "Are you sure you want to delete this special offer? This action cannot be undone.",
    async () => {
      try {
        const response = await fetch("../api/admin/offers/delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: offerId }),
          credentials: "same-origin"
        });
        
        const res = await response.json();
        if (res.success) {
          showToast("Offer deleted", "success");
          loadOffers();
        } else {
          showToast(res.message || "Failed to delete offer", "error");
        }
      } catch (err) {
        console.error("Error deleting offer:", err);
        showToast("An error occurred", "error");
      }
    }
  );
};

// Gallery Management Logic
let galleryItems = [];
let galleryFilter = "all";

async function loadGallery() {
  const container = document.getElementById("galleryGrid");
  if (!container) return;

  try {
    const response = await fetch("../api/admin/gallery/list.php", { credentials: "same-origin" });
    const res = await response.json();
    if (res.success) {
      galleryItems = res.data || [];
      renderGallery();
    } else {
      container.innerHTML = `<p style='text-align:center;'>${res.message || "Failed to load gallery"}</p>`;
    }
  } catch (err) {
    console.error("Error loading gallery:", err);
    container.innerHTML = "<p style='text-align:center;'>An error occurred while loading the gallery.</p>";
  }
}

function renderGallery() {
  const container = document.getElementById("galleryGrid");
  const searchTerm = document.getElementById("gallerySearch") ? document.getElementById("gallerySearch").value.toLowerCase() : "";
  
  if (!container) return;

  let filtered = galleryItems.filter(item => {
    const matchesFilter = galleryFilter === "all" || item.media_type === galleryFilter;
    const matchesSearch = item.title.toLowerCase().includes(searchTerm) || 
                         (item.tags && item.tags.toLowerCase().includes(searchTerm)) ||
                         (item.caption && item.caption.toLowerCase().includes(searchTerm));
    return matchesFilter && matchesSearch;
  });

  if (filtered.length === 0) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:4rem 2rem;color:hsl(var(--muted-foreground));opacity:0.7;font-size:1.1rem;width:100%;">No items found matching your criteria.</div>';
    return;
  }

  container.innerHTML = filtered.map(item => {
    const isVideo = item.media_type === "video";
    const tags = item.tags ? item.tags.split(',').map(t => t.trim()).filter(t => t) : [];
    
    return `
      <div class="gallery-item">
        <div class="gallery-media">
          ${isVideo ? `
            <video src="../${item.image_path}" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
            <div class="video-play-hint">
              <svg viewBox="0 0 24 24" fill="white" style="width:40px;height:40px;"><path d="M8 5v14l11-7z"/></svg>
            </div>
          ` : `
            <img src="../${item.image_path}" alt="${item.title}" loading="lazy">
          `}
          <div class="media-type-badge">
            ${isVideo ? `
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/></svg>
              VIDEO
            ` : `
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
              IMAGE
            `}
          </div>
        </div>
        <div class="gallery-info">
          <h4 class="gallery-item-title">${item.title}</h4>
          <p class="gallery-item-caption">${item.caption || "No caption"}</p>
          <div class="gallery-tags">
            ${tags.map(tag => `<span class="tag-badge">${tag}</span>`).join("")}
          </div>
        </div>
        <div class="gallery-actions">
          <button class="btn-icon" onclick="showGalleryModal(${item.id})" title="Edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="btn-icon text-destructive" onclick="deleteGalleryItem(${item.id})" title="Delete">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          </button>
        </div>
      </div>
    `;
  }).join("");
}

window.setGalleryFilter = function(filter) {
  galleryFilter = filter;
  document.querySelectorAll("[data-filter]").forEach(btn => {
    btn.classList.toggle("active", btn.dataset.filter === filter);
  });
  renderGallery();
};

window.filterGallery = function() {
  renderGallery();
};

window.showGalleryModal = function(itemId = null) {
  const modal = document.getElementById("galleryModal");
  const form = document.getElementById("galleryForm");
  const title = document.getElementById("galleryModalTitle");
  const preview = document.getElementById("galleryMediaPreview");
  
  form.reset();
  preview.style.display = "none";
  preview.innerHTML = "";
  document.getElementById("galleryUploadText").textContent = "Click to upload image or video";
  
  if (itemId) {
    const item = galleryItems.find(i => i.id == itemId);
    if (!item) return;
    
    title.textContent = "Edit Media Item";
    document.getElementById("galleryIdInput").value = item.id;
    document.getElementById("galleryTitleInput").value = item.title;
    document.getElementById("galleryCaptionInput").value = item.caption;
    document.getElementById("galleryTagsInput").value = item.tags;
    
    preview.style.display = "block";
    if (item.media_type === "video") {
      preview.innerHTML = `<video src="../${item.image_path}" controls style="max-width:100%; height:150px; border-radius:0.5rem;"></video>`;
    } else {
      preview.innerHTML = `<img src="../${item.image_path}" style="max-width:100%; height:150px; border-radius:0.5rem; object-fit:cover;">`;
    }
  } else {
    title.textContent = "Add Gallery Media";
    document.getElementById("galleryIdInput").value = "";
  }
  
  modal.classList.add("active");
};

// Handle media preview in modal
document.getElementById("galleryMediaInput").addEventListener("change", function(e) {
  const file = e.target.files[0];
  if (!file) return;
  
  const preview = document.getElementById("galleryMediaPreview");
  const uploadText = document.getElementById("galleryUploadText");
  const reader = new FileReader();
  
  uploadText.textContent = file.name;
  
  reader.onload = function(event) {
    preview.style.display = "block";
    if (file.type.startsWith("video/")) {
      preview.innerHTML = `<video src="${event.target.result}" controls style="max-width:100%; height:150px; border-radius:0.5rem;"></video>`;
    } else {
      preview.innerHTML = `<img src="${event.target.result}" style="max-width:100%; height:150px; border-radius:0.5rem; object-fit:cover;">`;
    }
  };
  
  reader.readAsDataURL(file);
});

/**
 * Handles the gallery submission for both creating and updating media items.
 * Performs media upload first if a file is selected, then updates the database record.
 */
window.handleGallerySubmit = async function() {
  const form = document.getElementById("galleryForm");
  const itemId = document.getElementById("galleryIdInput").value;
  const fileInput = document.getElementById("galleryMediaInput");
  const submitBtn = document.querySelector("#galleryModal .btn-primary");
  
  if (!document.getElementById("galleryTitleInput").value) {
    showToast("Title is required", "error");
    return;
  }

  try {
    submitBtn.disabled = true;
    submitBtn.textContent = itemId ? "Updating..." : "Creating...";
    
    let mediaPath = null;
    let mediaType = "image";
    
    // If a new file is chosen, upload it to the server first
    if (fileInput.files.length > 0) {
      const formData = new FormData();
      formData.append("file", fileInput.files[0]);
      
      const uploadRes = await fetch("../api/admin/gallery/upload.php", {
        method: "POST",
        body: formData,
        credentials: "same-origin"
      });
      
      const up = await uploadRes.json();
      if (up.success) {
        mediaPath = up.path;
        mediaType = up.media_type;
      } else {
        throw new Error(up.message || "Upload failed");
      }
    }

    // New items require an initial media file
    if (!itemId && !mediaPath) {
      showToast("Media file is required for new items", "error");
      submitBtn.disabled = false;
      submitBtn.textContent = "Save Media";
      return;
    }

    const payload = {
      id: itemId || null,
      title: document.getElementById("galleryTitleInput").value,
      caption: document.getElementById("galleryCaptionInput").value,
      tags: document.getElementById("galleryTagsInput").value,
    };
    
    if (mediaPath) {
      payload.image_path = mediaPath;
      payload.media_type = mediaType;
    }

    const endpoint = itemId ? "../api/admin/gallery/update.php" : "../api/admin/gallery/create.php";
    const response = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
      credentials: "same-origin"
    });

    const res = await response.json();
    if (res.success) {
      showToast(itemId ? "Media updated" : "Media added", "success");
      document.getElementById("galleryModal").classList.remove("active");
      loadGallery(); // Refresh the grid
    } else {
      showToast(res.message || "Failed to save item", "error");
    }
  } catch (err) {
    console.error("Error saving gallery item:", err);
    showToast(err.message || "An error occurred", "error");
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = "Save Media";
  }
};

window.deleteGalleryItem = function(itemId) {
  confirmDelete(
    "Delete Gallery Item",
    "Are you sure you want to delete this media item? This action cannot be undone.",
    async () => {
      try {
        const response = await fetch("../api/admin/gallery/delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ id: itemId }),
          credentials: "same-origin"
        });
        
        const res = await response.json();
        if (res.success) {
          showToast("Item deleted", "success");
          loadGallery();
        } else {
          showToast(res.message || "Failed to delete item", "error");
        }
      } catch (err) {
        console.error("Error deleting gallery item:", err);
        showToast("An error occurred", "error");
      }
    }
  );
};
