// Product UI functions
const productUI = {
    // Function to load products
    async loadProducts() {
        try {
            console.log('Loading products...');
            const products = await api.products.getAll();
            console.log('Products loaded:', products);
            this.displayProducts(products);
        } catch (error) {
            console.error('Error loading products:', error);
            document.getElementById('products-container').innerHTML = '<div class="col-12"><p class="text-center">Error loading products. Please try again later.</p></div>';
        }
    },

    // Function to display products
    displayProducts(products) {
        const container = document.getElementById('products-container');
        if (!container) return;

        console.log('Displaying products:', products);
        const defaultImage = '../assets/img/scarlett.png';
        console.log('Default image path:', defaultImage);

        container.innerHTML = products.map(product => {
            // Check if ImageURL is valid (not empty and not a placeholder)
            const hasValidImage = product.ImageURL && 
                                product.ImageURL !== 'test.jpg' && 
                                !product.ImageURL.includes('example.com');
            const imageUrl = hasValidImage ? product.ImageURL : defaultImage;
            console.log('Product image URL:', imageUrl);
            
            return `
            <div class="col-md-4">
                <div class="card mb-4 product-wap rounded-0">
                    <div class="card rounded-0">
                        <img class="card-img rounded-0 img-fluid" src="${imageUrl}" alt="${product.Name}" onerror="this.src='${defaultImage}'">
                        <div class="card-img-overlay rounded-0 product-overlay d-flex align-items-center justify-content-center">
                            <ul class="list-unstyled">
                                <li><a class="btn btn-success text-white" href="product-detail.html?id=${product.ProductID}"><i class="far fa-eye"></i></a></li>
                                <li><a class="btn btn-success text-white mt-2" href="#" onclick="productUI.addToCart(${product.ProductID})"><i class="fas fa-cart-plus"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <a href="product-detail.html?id=${product.ProductID}" class="h3 text-decoration-none">${product.Name}</a>
                        <ul class="w-100 list-unstyled d-flex justify-content-between mb-0">
                            <li>${product.Brand || 'Unknown Brand'}</li>
                            <li class="pt-2">
                                <span class="product-color-dot color-dot-red float-left rounded-circle ml-1"></span>
                                <span class="product-color-dot color-dot-blue float-left rounded-circle ml-1"></span>
                                <span class="product-color-dot color-dot-black float-left rounded-circle ml-1"></span>
                                <span class="product-color-dot color-dot-light float-left rounded-circle ml-1"></span>
                                <span class="product-color-dot color-dot-green float-left rounded-circle ml-1"></span>
                            </li>
                        </ul>
                        <ul class="list-unstyled d-flex justify-content-center mb-1">
                            <li>
                                <i class="text-warning fa fa-star"></i>
                                <i class="text-warning fa fa-star"></i>
                                <i class="text-warning fa fa-star"></i>
                                <i class="text-warning fa fa-star"></i>
                                <i class="text-muted fa fa-star"></i>
                            </li>
                        </ul>
                        <p class="text-center mb-0">$${product.Price || '0.00'}</p>
                    </div>
                </div>
            </div>
        `}).join('');
    },

    // Function to load a single product
    async loadProductDetails() {
        const urlParams = new URLSearchParams(window.location.search);
        const productId = urlParams.get('id');
        if (!productId) {
            console.error('No product ID provided');
            return;
        }

        try {
            console.log('Loading product details for ID:', productId);
            const product = await api.products.getById(productId);
            console.log('Product details loaded:', product);
            
            // Fetch category name if category ID exists
            if (product.CategoryID) {
                try {
                    const category = await api.categories.getById(product.CategoryID);
                    console.log('Category data:', category);
                    product.CategoryName = category.CategoryName;
                } catch (error) {
                    console.error('Error loading category:', error);
                    product.CategoryName = 'Uncategorized';
                }
            }
            
            this.displayProductDetails(product);
        } catch (error) {
            console.error('Error loading product details:', error);
            document.querySelector('.card-body').innerHTML = '<p class="text-center">Error loading product details. Please try again later.</p>';
        }
    },

    // Function to display product details
    displayProductDetails(product) {
        console.log('Displaying product details:', product);
        
        // Update product image
        const productImage = document.getElementById('product-detail');
        if (productImage) {
            // Check if ImageURL is valid (not empty and not a placeholder)
            const hasValidImage = product.ImageURL && 
                                product.ImageURL !== 'test.jpg' && 
                                !product.ImageURL.includes('example.com');
            const imageUrl = hasValidImage ? product.ImageURL : '../assets/img/scarlett.png';
            console.log('Product detail image URL:', imageUrl);
            productImage.src = imageUrl;
            productImage.alt = product.Name;
            productImage.onerror = function() {
                console.log('Product detail image failed to load:', this.src);
                this.src = '../assets/img/scarlett.png';
            };
        }
        
        // Update product title
        const titleElement = document.getElementById('product-name');
        if (titleElement) {
            titleElement.textContent = product.Name || 'Product Name';
        }
        
        // Update product price
        const priceElement = document.getElementById('product-price');
        if (priceElement) {
            priceElement.textContent = `$${product.Price || '0.00'}`;
        }
        
        // Update product brand
        const brandElement = document.getElementById('product-brand');
        if (brandElement) {
            brandElement.textContent = product.Brand || 'Brand Name';
        }
        
        // Update product description
        const descriptionElement = document.getElementById('product-description');
        if (descriptionElement) {
            descriptionElement.textContent = product.Description || 'No description available.';
        }
        
        // Update product category
        const categoryElement = document.getElementById('product-category');
        if (categoryElement) {
            categoryElement.textContent = product.CategoryName || 'Uncategorized';
        }
    },

    // Function to add product to cart
    async addToCart(productId) {
        try {
            console.log('Adding product to cart:', productId);
            // First get the product details
            const product = await api.products.getById(productId);
            if (!product) {
                throw new Error('Product not found');
            }
            
            // Add to cart with full product details
            const cartProduct = {
                id: product.ProductID,
                name: product.Name || 'Unknown Product',
                price: parseFloat(product.Price) || 0,
                image: product.ImageURL || '../assets/img/scarlett.png'
            };
            
            // Check if cart is available
            if (typeof api.cart === 'undefined') {
                console.error('Cart is not available');
                alert('Error: Cart functionality is not available. Please refresh the page and try again.');
                return;
            }
            
            api.cart.addToCart(cartProduct);
            alert('Product added to cart!');
            this.updateCartBadge();
        } catch (error) {
            console.error('Error adding to cart:', error);
            alert('Error adding to cart');
        }
    },

    // Function to update cart badge
    updateCartBadge() {
        const badge = document.querySelector('.fa-cart-arrow-down + .badge');
        if (badge) {
            api.cart.getCart().then(cart => {
                const count = cart.reduce((total, item) => total + item.quantity, 0);
                badge.textContent = count;
            });
        }
    }
};

// Export the productUI object
window.productUI = productUI;