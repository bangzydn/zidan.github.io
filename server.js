const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const multer = require('multer');
const moment = require('moment');
const { v4: uuidv4 } = require('uuid');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;
const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';

// Middleware
app.use(helmet());
app.use(cors());
app.use(express.json());
app.use(express.static('public'));
app.use('/assets', express.static('assets'));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100 // limit each IP to 100 requests per windowMs
});
app.use(limiter);

// Database initialization
const db = new sqlite3.Database('erp.db');

// Create tables
db.serialize(() => {
  // Users table
  db.run(`CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // Chart of Accounts
  db.run(`CREATE TABLE IF NOT EXISTS chart_of_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    account_code TEXT UNIQUE NOT NULL,
    account_name TEXT NOT NULL,
    account_type TEXT NOT NULL,
    parent_account_id INTEGER,
    balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // Journal Entries
  db.run(`CREATE TABLE IF NOT EXISTS journal_entries (
    id INTEGER PRIMARY KEY AUTOINESTAMP,
    entry_number TEXT UNIQUE NOT NULL,
    entry_date DATE NOT NULL,
    reference TEXT,
    description TEXT,
    total_debit DECIMAL(15,2) DEFAULT 0,
    total_credit DECIMAL(15,2) DEFAULT 0,
    status TEXT DEFAULT 'draft',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users (id)
  )`);

  // Journal Entry Lines
  db.run(`CREATE TABLE IF NOT EXISTS journal_entry_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    journal_entry_id INTEGER NOT NULL,
    account_id INTEGER NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    FOREIGN KEY (journal_entry_id) REFERENCES journal_entries (id),
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts (id)
  )`);

  // Customers
  db.run(`CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_code TEXT UNIQUE NOT NULL,
    customer_name TEXT NOT NULL,
    email TEXT,
    phone TEXT,
    address TEXT,
    credit_limit DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // Suppliers
  db.run(`CREATE TABLE IF NOT EXISTS suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplier_code TEXT UNIQUE NOT NULL,
    supplier_name TEXT NOT NULL,
    email TEXT,
    phone TEXT,
    address TEXT,
    credit_limit DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // Products/Inventory
  db.run(`CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_code TEXT UNIQUE NOT NULL,
    product_name TEXT NOT NULL,
    description TEXT,
    category TEXT,
    unit_price DECIMAL(15,2) DEFAULT 0,
    cost_price DECIMAL(15,2) DEFAULT 0,
    stock_quantity INTEGER DEFAULT 0,
    reorder_level INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // Sales Orders
  db.run(`CREATE TABLE IF NOT EXISTS sales_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number TEXT UNIQUE NOT NULL,
    customer_id INTEGER NOT NULL,
    order_date DATE NOT NULL,
    due_date DATE,
    total_amount DECIMAL(15,2) DEFAULT 0,
    status TEXT DEFAULT 'draft',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers (id),
    FOREIGN KEY (created_by) REFERENCES users (id)
  )`);

  // Sales Order Lines
  db.run(`CREATE TABLE IF NOT EXISTS sales_order_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sales_order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
  )`);

  // Purchase Orders
  db.run(`CREATE TABLE IF NOT EXISTS purchase_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number TEXT UNIQUE NOT NULL,
    supplier_id INTEGER NOT NULL,
    order_date DATE NOT NULL,
    expected_date DATE,
    total_amount DECIMAL(15,2) DEFAULT 0,
    status TEXT DEFAULT 'draft',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers (id),
    FOREIGN KEY (created_by) REFERENCES users (id)
  )`);

  // Purchase Order Lines
  db.run(`CREATE TABLE IF NOT EXISTS purchase_order_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
  )`);

  // Invoices
  db.run(`CREATE TABLE IF NOT EXISTS invoices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_number TEXT UNIQUE NOT NULL,
    customer_id INTEGER NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    total_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    status TEXT DEFAULT 'draft',
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers (id),
    FOREIGN KEY (created_by) REFERENCES users (id)
  )`);

  // Invoice Lines
  db.run(`CREATE TABLE IF NOT EXISTS invoice_lines (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices (id),
    FOREIGN KEY (product_id) REFERENCES products (id)
  )`);

  // Payments
  db.run(`CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    payment_number TEXT UNIQUE NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method TEXT,
    reference TEXT,
    customer_id INTEGER,
    supplier_id INTEGER,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers (id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers (id),
    FOREIGN KEY (created_by) REFERENCES users (id)
  )`);

  // Insert default admin user
  const adminPassword = bcrypt.hashSync('admin123', 10);
  db.run(`INSERT OR IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, ?)`, 
    ['admin', 'admin@erp.com', adminPassword, 'admin']);

  // Insert default chart of accounts
  const defaultAccounts = [
    ['1000', 'Cash', 'asset', null],
    ['1100', 'Accounts Receivable', 'asset', null],
    ['1200', 'Inventory', 'asset', null],
    ['2000', 'Accounts Payable', 'liability', null],
    ['3000', 'Owner Equity', 'equity', null],
    ['4000', 'Sales Revenue', 'revenue', null],
    ['5000', 'Cost of Goods Sold', 'expense', null],
    ['6000', 'Operating Expenses', 'expense', null]
  ];

  defaultAccounts.forEach(account => {
    db.run(`INSERT OR IGNORE INTO chart_of_accounts (account_code, account_name, account_type, parent_account_id) VALUES (?, ?, ?, ?)`, account);
  });
});

// Authentication middleware
const authenticateToken = (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];

  if (!token) {
    return res.status(401).json({ error: 'Access token required' });
  }

  jwt.verify(token, JWT_SECRET, (err, user) => {
    if (err) {
      return res.status(403).json({ error: 'Invalid token' });
    }
    req.user = user;
    next();
  });
};

// Routes
app.post('/api/auth/login', (req, res) => {
  const { username, password } = req.body;
  
  db.get('SELECT * FROM users WHERE username = ?', [username], (err, user) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    
    if (!user || !bcrypt.compareSync(password, user.password)) {
      return res.status(401).json({ error: 'Invalid credentials' });
    }

    const token = jwt.sign({ id: user.id, username: user.username, role: user.role }, JWT_SECRET, { expiresIn: '24h' });
    res.json({ token, user: { id: user.id, username: user.username, role: user.role } });
  });
});

// Dashboard API
app.get('/api/dashboard/stats', authenticateToken, (req, res) => {
  const stats = {};
  
  // Get total customers
  db.get('SELECT COUNT(*) as count FROM customers WHERE is_active = 1', (err, result) => {
    if (err) return res.status(500).json({ error: 'Database error' });
    stats.totalCustomers = result.count;
    
    // Get total suppliers
    db.get('SELECT COUNT(*) as count FROM suppliers WHERE is_active = 1', (err, result) => {
      if (err) return res.status(500).json({ error: 'Database error' });
      stats.totalSuppliers = result.count;
      
      // Get total products
      db.get('SELECT COUNT(*) as count FROM products WHERE is_active = 1', (err, result) => {
        if (err) return res.status(500).json({ error: 'Database error' });
        stats.totalProducts = result.count;
        
        // Get total sales this month
        const currentMonth = moment().format('YYYY-MM');
        db.get('SELECT COALESCE(SUM(total_amount), 0) as total FROM sales_orders WHERE strftime("%Y-%m", order_date) = ? AND status = "completed"', 
          [currentMonth], (err, result) => {
          if (err) return res.status(500).json({ error: 'Database error' });
          stats.monthlySales = result.total;
          
          res.json(stats);
        });
      });
    });
  });
});

// Chart of Accounts API
app.get('/api/accounts', authenticateToken, (req, res) => {
  db.all('SELECT * FROM chart_of_accounts ORDER BY account_code', (err, accounts) => {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json(accounts);
  });
});

app.post('/api/accounts', authenticateToken, (req, res) => {
  const { account_code, account_name, account_type, parent_account_id } = req.body;
  
  db.run('INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_account_id) VALUES (?, ?, ?, ?)',
    [account_code, account_name, account_type, parent_account_id], function(err) {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json({ id: this.lastID, message: 'Account created successfully' });
  });
});

// Customers API
app.get('/api/customers', authenticateToken, (req, res) => {
  db.all('SELECT * FROM customers ORDER BY customer_name', (err, customers) => {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json(customers);
  });
});

app.post('/api/customers', authenticateToken, (req, res) => {
  const { customer_code, customer_name, email, phone, address, credit_limit } = req.body;
  
  db.run('INSERT INTO customers (customer_code, customer_name, email, phone, address, credit_limit) VALUES (?, ?, ?, ?, ?, ?)',
    [customer_code, customer_name, email, phone, address, credit_limit], function(err) {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json({ id: this.lastID, message: 'Customer created successfully' });
  });
});

// Products API
app.get('/api/products', authenticateToken, (req, res) => {
  db.all('SELECT * FROM products ORDER BY product_name', (err, products) => {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json(products);
  });
});

app.post('/api/products', authenticateToken, (req, res) => {
  const { product_code, product_name, description, category, unit_price, cost_price, stock_quantity, reorder_level } = req.body;
  
  db.run('INSERT INTO products (product_code, product_name, description, category, unit_price, cost_price, stock_quantity, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    [product_code, product_name, description, category, unit_price, cost_price, stock_quantity, reorder_level], function(err) {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json({ id: this.lastID, message: 'Product created successfully' });
  });
});

// Sales Orders API
app.get('/api/sales-orders', authenticateToken, (req, res) => {
  db.all(`
    SELECT so.*, c.customer_name 
    FROM sales_orders so 
    JOIN customers c ON so.customer_id = c.id 
    ORDER BY so.order_date DESC
  `, (err, orders) => {
    if (err) return res.status(500).json({ error: 'Database error' });
    res.json(orders);
  });
});

app.post('/api/sales-orders', authenticateToken, (req, res) => {
  const { customer_id, order_date, due_date, total_amount, lines } = req.body;
  const order_number = 'SO-' + Date.now();
  
  db.run('INSERT INTO sales_orders (order_number, customer_id, order_date, due_date, total_amount, created_by) VALUES (?, ?, ?, ?, ?, ?)',
    [order_number, customer_id, order_date, due_date, total_amount, req.user.id], function(err) {
    if (err) return res.status(500).json({ error: 'Database error' });
    
    const orderId = this.lastID;
    
    // Insert order lines
    if (lines && lines.length > 0) {
      const stmt = db.prepare('INSERT INTO sales_order_lines (sales_order_id, product_id, quantity, unit_price, total_amount) VALUES (?, ?, ?, ?, ?)');
      lines.forEach(line => {
        stmt.run([orderId, line.product_id, line.quantity, line.unit_price, line.total_amount]);
      });
      stmt.finalize();
    }
    
    res.json({ id: orderId, message: 'Sales order created successfully' });
  });
});

// Serve the main application
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start server
app.listen(PORT, () => {
  console.log(`ERP Server running on port ${PORT}`);
  console.log(`Open http://localhost:${PORT} in your browser`);
});

module.exports = app;