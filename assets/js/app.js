// 👉 Categorieën laden
async function loadCategories() {
    const res = await fetch('/harries-helden-module7.1/api/categories.php');
    const categories = await res.json();

    const list = document.getElementById('category-list');
    list.innerHTML = '';

    categories.forEach((cat, index) => {

        const btn = document.createElement('a');
        btn.href = "#";
        btn.className = "cat-btn";
        btn.innerText = cat.name;

        btn.onclick = (e) => {
            e.preventDefault();

            document.querySelectorAll('.cat-btn')
                .forEach(b => b.classList.remove('active'));

            btn.classList.add('active');
            loadProducts(cat.category_id);
        };

        list.appendChild(btn);

        if (index === 0) {
            btn.classList.add('active');
        }
    });
}


// 👉 Producten laden
async function loadProducts(cat = 1) {
    const res = await fetch(`/harries-helden-module7.1/api/products.php?cat=${cat}`);
    const products = await res.json();

    const container = document.querySelector('.products');
    container.innerHTML = '';

    products.forEach(p => {
        container.innerHTML += `
            <div class="card" onclick="openProduct(${p.product_id})">
                <img src="${p.filename}">
                <div class="card-body">
                    <h3>${p.name}</h3>
                    <p>${p.kcal} kcal</p>
                    <div class="card-footer">
                        <span>€${parseFloat(p.price).toFixed(2)}</span>
                        <button onclick="event.stopPropagation(); addToCart(${p.product_id})">+</button>
                    </div>
                </div>
            </div>
        `;
    });
}


// 👉 Toevoegen aan winkelmandje via API
async function addToCart(id) {
    await fetch('/harries-helden-module7.1/api/cart.php', {
        method: "POST",
        body: JSON.stringify({ product_id: id })
    });

    updateCartInfo();
}


// 👉 Toevoegen + popup sluiten
async function addToCartAndClose(id) {
    await addToCart(id);
    closePopup();
}


// 👉 Winkelmandje info updaten
async function updateCartInfo() {
    const res = await fetch('/harries-helden-module7.1/api/order.php');
    const data = await res.json();

    const bar = document.getElementById('cart-info');

    if (data.error) {
        bar.innerHTML = `<strong>0 ${translations.items}</strong> €0.00`;
        return;
    }

    bar.innerHTML = `<strong>${data.items.length} ${translations.items}</strong> €${data.total.toFixed(2)}`;
}


// 👉 Product popup openen
function openProduct(id) {
    fetch(`/harries-helden-module7.1/api/product.php?id=${id}`)
        .then(res => res.json())
        .then(p => {
            const overlay = document.getElementById('popup-overlay');
            const popup = document.getElementById('popup');

            const ingredients = p.ingredienten
                ? p.ingredienten.split(',').map(i => i.trim())
                : [];

            overlay.style.display = 'flex';

            popup.innerHTML = `
                <h2>${p.name}</h2>
                <img src="${p.filename}" style="width:100%; border-radius:15px;">
                <p>${p.kcal} kcal</p>
                <p>€${parseFloat(p.price).toFixed(2)}</p>

                <button id="edit-ingredients-btn">
                    ${translations.customize_ingredients}
                </button>

                <div id="ingredient-list" class="ingredient-list" style="display:none; margin-top:20px;">
                    ${ingredients.map(i => `
                        <label>
                            <input type="checkbox" checked value="${i}">
                            ${i}
                        </label>
                    `).join('')}
                </div>

                <button onclick="addToCartAndClose(${p.product_id})" style="margin-top:20px;">
                    ${translations.add_to_cart}
                </button>

                <div id="cross-sell-section"></div>

                <button onclick="closePopup()" style="margin-top:10px;">
                    ${translations.close}
                </button>
            `;

            document.getElementById('edit-ingredients-btn').onclick = () => {
                document.getElementById('ingredient-list').style.display = 'block';
            };

            // Load cross-sell suggestions
            loadCrossSell(p.product_id);
        });
}


// 👉 Cross-sell producten laden
async function loadCrossSell(productId) {
    try {
        const res = await fetch(`/harries-helden-module7.1/api/cross_sell.php?product_id=${productId}`);
        const products = await res.json();

        const section = document.getElementById('cross-sell-section');
        if (!products.length) {
            section.innerHTML = '';
            return;
        }

        const cards = products.map(p => `
            <div class="cross-sell-card">
                <img src="${p.filename}" alt="${p.name}">
                <div class="cross-sell-info">
                    <span class="cross-sell-name">${p.name}</span>
                    <span class="cross-sell-price">€${parseFloat(p.price).toFixed(2)}</span>
                </div>
                <button class="cross-sell-add" onclick="event.stopPropagation(); addToCart(${p.product_id})">+</button>
            </div>
        `).join('');

        section.innerHTML = `
            <div class="cross-sell-container">
                <h3>🍽️ ${translations.cross_sell_title}</h3>
                <div class="cross-sell-scroll">${cards}</div>
            </div>
        `;
    } catch (err) {
        console.error('Failed to load cross-sell products:', err);
    }
}


// 👉 Popup sluiten
function closePopup() {
    document.getElementById('popup-overlay').style.display = 'none';
}


// 👉 Startpagina laden
loadCategories();
loadProducts(1);
updateCartInfo();