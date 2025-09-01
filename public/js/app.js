// Retail MSME ERP Application
class RetailERP {
    constructor() {
        this.apiBase = '/api';
        this.token = localStorage.getItem('erp_token');
        this.user = JSON.parse(localStorage.getItem('erp_user') || '{}');
        this.currentSection = 'dashboard';
        this.salesChart = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuth();
        this.initializeCharts();
    }

    setupEventListeners() {
        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => this.handleLogin(e));
        
        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => this.handleNavigation(e));
        });
        
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', () => this.toggleSidebar());
        
        // Logout
        document.getElementById('logoutBtn').addEventListener('click', () => this.handleLogout());
        
        // Modal buttons
        document.getElementById('addAccountBtn').addEventListener('click', () => this.showModal('addAccountModal'));
        document.getElementById('addProductBtn').addEventListener('click', () => this.showModal('addProductModal'));
        document.getElementById('addCustomerBtn').addEventListener('click', () => this.showModal('addCustomerModal'));
        document.getElementById('addSupplierBtn').addEventListener('click', () => this.showModal('addSupplierModal'));
        
        // Modal cancel buttons
        document.getElementById('cancelAddAccount').addEventListener('click', () => this.hideModal('addAccountModal'));
        document.getElementById('cancelAddProduct').addEventListener('click', () => this.hideModal('addProductModal'));
        document.getElementById('cancelAddCustomer').addEventListener('click', () => this.hideModal('addCustomerModal'));
        document.getElementById('cancelAddSupplier').addEventListener('click', () => this.hideModal('addSupplierModal'));
        
        // Form submissions
        document.getElementById('addAccountForm').addEventListener('submit', (e) => this.handleAddAccount(e));
        document.getElementById('addProductForm').addEventListener('submit', (e) => this.handleAddProduct(e));
        document.getElementById('addCustomerForm').addEventListener('submit', (e) => this.handleAddCustomer(e));
        document.getElementById('addSupplierForm').addEventListener('submit', (e) => this.handleAddSupplier(e));
    }

    checkAuth() {
        if (this.token) {
            this.showMainApp();
            this.loadDashboardData();
        } else {
            this.showLoginModal();
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await fetch(`${this.apiBase}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.token = data.token;
                this.user = data.user;
                
                localStorage.setItem('erp_token', this.token);
                localStorage.setItem('erp_user', JSON.stringify(this.user));
                
                this.showMainApp();
                this.loadDashboardData();
                this.showNotification('Login successful!', 'success');
            } else {
                this.showNotification(data.error || 'Login failed', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    }

    handleLogout() {
        this.token = null;
        this.user = {};
        localStorage.removeItem('erp_token');
        localStorage.removeItem('erp_user');
        this.showLoginModal();
        this.showNotification('Logged out successfully', 'success');
    }

    showLoginModal() {
        document.getElementById('loginModal').classList.remove('hidden');
        document.getElementById('mainApp').classList.add('hidden');
    }

    showMainApp() {
        document.getElementById('loginModal').classList.add('hidden');
        document.getElementById('mainApp').classList.remove('hidden');
        document.getElementById('userDisplayName').textContent = this.user.username;
    }

    handleNavigation(e) {
        e.preventDefault();
        const section = e.currentTarget.getAttribute('data-section');
        this.showSection(section);
    }

    showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(section => {
            section.classList.add('hidden');
        });
        
        // Show selected section
        document.getElementById(sectionName).classList.remove('hidden');
        
        // Update navigation active state
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('bg-gray-800', 'text-white');
            item.classList.add('text-gray-300');
        });
        
        document.querySelector(`[data-section="${sectionName}"]`).classList.add('bg-gray-800', 'text-white');
        document.querySelector(`[data-section="${sectionName}"]`).classList.remove('text-gray-300');
        
        // Update page title
        const titles = {
            dashboard: 'Dashboard',
            accounting: 'Accounting',
            inventory: 'Inventory Management',
            customers: 'Customer Management',
            suppliers: 'Supplier Management',
            sales: 'Sales Orders',
            purchases: 'Purchase Orders',
            reports: 'Reports & Analytics',
            settings: 'System Settings'
        };
        
        document.getElementById('pageTitle').textContent = titles[sectionName] || 'Dashboard';
        this.currentSection = sectionName;
        
        // Load section-specific data
        this.loadSectionData(sectionName);
    }

    async loadSectionData(sectionName) {
        switch (sectionName) {
            case 'accounting':
                await this.loadAccounts();
                break;
            case 'inventory':
                await this.loadProducts();
                break;
            case 'customers':
                await this.loadCustomers();
                break;
            case 'suppliers':
                await this.loadSuppliers();
                break;
            case 'sales':
                await this.loadSalesOrders();
                break;
            case 'purchases':
                await this.loadPurchaseOrders();
                break;
        }
    }

    async loadDashboardData() {
        try {
            const response = await fetch(`${this.apiBase}/dashboard/stats`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const stats = await response.json();
                this.updateDashboardStats(stats);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    updateDashboardStats(stats) {
        document.getElementById('totalCustomers').textContent = stats.totalCustomers || 0;
        document.getElementById('totalProducts').textContent = stats.totalProducts || 0;
        document.getElementById('totalSuppliers').textContent = stats.totalSuppliers || 0;
        document.getElementById('monthlySales').textContent = `$${(stats.monthlySales || 0).toFixed(2)}`;
    }

    async loadAccounts() {
        try {
            const response = await fetch(`${this.apiBase}/accounts`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const accounts = await response.json();
                this.renderAccountsTable(accounts);
            }
        } catch (error) {
            console.error('Error loading accounts:', error);
        }
    }

    renderAccountsTable(accounts) {
        const tbody = document.getElementById('accountsTableBody');
        tbody.innerHTML = '';
        
        accounts.forEach(account => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${account.account_code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${account.account_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${this.getAccountTypeColor(account.account_type)}">
                        ${account.account_type}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(account.balance || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${account.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    getAccountTypeColor(type) {
        const colors = {
            asset: 'bg-blue-100 text-blue-800',
            liability: 'bg-red-100 text-red-800',
            equity: 'bg-purple-100 text-purple-800',
            revenue: 'bg-green-100 text-green-800',
            expense: 'bg-yellow-100 text-yellow-800'
        };
        return colors[type] || 'bg-gray-100 text-gray-800';
    }

    async loadProducts() {
        try {
            const response = await fetch(`${this.apiBase}/products`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const products = await response.json();
                this.renderProductsTable(products);
            }
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    renderProductsTable(products) {
        const tbody = document.getElementById('productsTableBody');
        tbody.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.product_code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.product_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${product.category || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${product.stock_quantity <= product.reorder_level ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                        ${product.stock_quantity}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(product.unit_price || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${product.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${product.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadCustomers() {
        try {
            const response = await fetch(`${this.apiBase}/customers`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const customers = await response.json();
                this.renderCustomersTable(customers);
            }
        } catch (error) {
            console.error('Error loading customers:', error);
        }
    }

    renderCustomersTable(customers) {
        const tbody = document.getElementById('customersTableBody');
        tbody.innerHTML = '';
        
        customers.forEach(customer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${customer.customer_code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${customer.customer_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${customer.email || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${customer.phone || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(customer.balance || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${customer.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${customer.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadSuppliers() {
        try {
            const response = await fetch(`${this.apiBase}/suppliers`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const suppliers = await response.json();
                this.renderSuppliersTable(suppliers);
            }
        } catch (error) {
            console.error('Error loading suppliers:', error);
        }
    }

    renderSuppliersTable(suppliers) {
        const tbody = document.getElementById('suppliersTableBody');
        tbody.innerHTML = '';
        
        suppliers.forEach(supplier => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${supplier.supplier_code}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplier.supplier_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplier.email || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${supplier.phone || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(supplier.balance || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${supplier.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${supplier.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadSalesOrders() {
        try {
            const response = await fetch(`${this.apiBase}/sales-orders`, {
                headers: {
                    'Authorization': `Bearer ${this.token}`
                }
            });
            
            if (response.ok) {
                const orders = await response.json();
                this.renderSalesOrdersTable(orders);
            }
        } catch (error) {
            console.error('Error loading sales orders:', error);
        }
    }

    renderSalesOrdersTable(orders) {
        const tbody = document.getElementById('salesOrdersTableBody');
        tbody.innerHTML = '';
        
        orders.forEach(order => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${order.order_number}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.customer_name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.order_date}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${order.due_date || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$${parseFloat(order.total_amount || 0).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        ${this.getOrderStatusColor(order.status)}">
                        ${order.status}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                    <button class="text-green-600 hover:text-green-900 mr-3">Edit</button>
                    <button class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    async loadPurchaseOrders() {
        // This would be implemented similar to sales orders
        // For now, we'll show a placeholder
        const tbody = document.getElementById('purchaseOrdersTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                    Purchase orders functionality coming soon...
                </td>
            </tr>
        `;
    }

    getOrderStatusColor(status) {
        const colors = {
            draft: 'bg-gray-100 text-gray-800',
            confirmed: 'bg-blue-100 text-blue-800',
            processing: 'bg-yellow-100 text-yellow-800',
            completed: 'bg-green-100 text-green-800',
            cancelled: 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    async handleAddAccount(e) {
        e.preventDefault();
        
        const formData = {
            account_code: document.getElementById('accountCode').value,
            account_name: document.getElementById('accountName').value,
            account_type: document.getElementById('accountType').value
        };
        
        try {
            const response = await fetch(`${this.apiBase}/accounts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                this.showNotification('Account created successfully!', 'success');
                this.hideModal('addAccountModal');
                document.getElementById('addAccountForm').reset();
                this.loadAccounts();
            } else {
                const data = await response.json();
                this.showNotification(data.error || 'Failed to create account', 'error');
            }
        } catch (error) {
            console.error('Error creating account:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    }

    async handleAddProduct(e) {
        e.preventDefault();
        
        const formData = {
            product_code: document.getElementById('productCode').value,
            product_name: document.getElementById('productName').value,
            category: document.getElementById('productCategory').value,
            unit_price: parseFloat(document.getElementById('productUnitPrice').value),
            cost_price: parseFloat(document.getElementById('productCostPrice').value),
            stock_quantity: parseInt(document.getElementById('productStock').value)
        };
        
        try {
            const response = await fetch(`${this.apiBase}/products`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                this.showNotification('Product created successfully!', 'success');
                this.hideModal('addProductModal');
                document.getElementById('addProductForm').reset();
                this.loadProducts();
            } else {
                const data = await response.json();
                this.showNotification(data.error || 'Failed to create product', 'error');
            }
        } catch (error) {
            console.error('Error creating product:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    }

    async handleAddCustomer(e) {
        e.preventDefault();
        
        const formData = {
            customer_code: document.getElementById('customerCode').value,
            customer_name: document.getElementById('customerName').value,
            email: document.getElementById('customerEmail').value,
            phone: document.getElementById('customerPhone').value,
            address: document.getElementById('customerAddress').value,
            credit_limit: parseFloat(document.getElementById('customerCreditLimit').value)
        };
        
        try {
            const response = await fetch(`${this.apiBase}/customers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                this.showNotification('Customer created successfully!', 'success');
                this.hideModal('addCustomerModal');
                document.getElementById('addCustomerForm').reset();
                this.loadCustomers();
                this.loadDashboardData(); // Refresh dashboard stats
            } else {
                const data = await response.json();
                this.showNotification(data.error || 'Failed to create customer', 'error');
            }
        } catch (error) {
            console.error('Error creating customer:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    }

    async handleAddSupplier(e) {
        e.preventDefault();
        
        const formData = {
            supplier_code: document.getElementById('supplierCode').value,
            supplier_name: document.getElementById('supplierName').value,
            email: document.getElementById('supplierEmail').value,
            phone: document.getElementById('supplierPhone').value,
            address: document.getElementById('supplierAddress').value,
            credit_limit: parseFloat(document.getElementById('supplierCreditLimit').value)
        };
        
        try {
            const response = await fetch(`${this.apiBase}/suppliers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.token}`
                },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                this.showNotification('Supplier created successfully!', 'success');
                this.hideModal('addSupplierModal');
                document.getElementById('addSupplierForm').reset();
                this.loadSuppliers();
                this.loadDashboardData(); // Refresh dashboard stats
            } else {
                const data = await response.json();
                this.showNotification(data.error || 'Failed to create supplier', 'error');
            }
        } catch (error) {
            console.error('Error creating supplier:', error);
            this.showNotification('Network error. Please try again.', 'error');
        }
    }

    showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const isHidden = sidebar.classList.contains('-translate-x-full');
        
        if (isHidden) {
            sidebar.classList.remove('-translate-x-full');
        } else {
            sidebar.classList.add('-translate-x-full');
        }
    }

    initializeCharts() {
        // Initialize sales chart
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            this.salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
}

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RetailERP();
});