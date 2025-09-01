# Retail MSME ERP - Complete Business Management Solution

A comprehensive, modern ERP (Enterprise Resource Planning) system designed specifically for retail Micro, Small, and Medium Enterprises (MSMEs). Built with Node.js, Express.js, SQLite, and a beautiful Tailwind CSS frontend.

## 🚀 Features

### Core Modules

#### 1. **Dashboard & Analytics**
- Real-time business metrics overview
- Interactive charts and visualizations
- Key performance indicators (KPIs)
- Recent activity feed
- Responsive design for all devices

#### 2. **Accounting & Finance**
- Complete Chart of Accounts management
- Double-entry bookkeeping system
- Journal entries with audit trail
- Financial statements preparation
- Multi-currency support (configurable)

#### 3. **Inventory Management**
- Product catalog management
- Stock level tracking
- Reorder level alerts
- Cost and selling price management
- Category-based organization
- Barcode support ready

#### 4. **Customer Relationship Management (CRM)**
- Customer database management
- Contact information tracking
- Credit limit management
- Customer balance tracking
- Sales history

#### 5. **Supplier Management**
- Vendor database
- Purchase order management
- Supplier credit limits
- Payment tracking
- Performance metrics

#### 6. **Sales Management**
- Sales order processing
- Customer invoicing
- Payment collection
- Sales analytics
- Order status tracking

#### 7. **Purchase Management**
- Purchase order creation
- Supplier management
- Receiving and inspection
- Cost tracking
- Inventory updates

#### 8. **Reporting & Analytics**
- Financial reports (Balance Sheet, P&L, Cash Flow)
- Business intelligence dashboards
- Custom report generation
- Export capabilities (PDF, Excel ready)
- Real-time data visualization

#### 9. **User Management & Security**
- Role-based access control
- Secure authentication (JWT)
- User activity logging
- Password security (bcrypt)
- Session management

## 🛠️ Technology Stack

### Backend
- **Node.js** - JavaScript runtime
- **Express.js** - Web application framework
- **SQLite3** - Lightweight database
- **JWT** - JSON Web Token authentication
- **bcryptjs** - Password hashing
- **Helmet** - Security middleware
- **CORS** - Cross-origin resource sharing
- **Rate Limiting** - API protection

### Frontend
- **HTML5** - Semantic markup
- **Tailwind CSS** - Utility-first CSS framework
- **Vanilla JavaScript** - Modern ES6+ features
- **Chart.js** - Interactive charts
- **Font Awesome** - Icon library
- **Responsive Design** - Mobile-first approach

### Database
- **SQLite** - File-based database
- **Normalized Schema** - Proper database design
- **Foreign Key Constraints** - Data integrity
- **Indexes** - Performance optimization

## 📋 Prerequisites

Before running this application, make sure you have:

- **Node.js** (version 14 or higher)
- **npm** (Node Package Manager)
- **Git** (for cloning the repository)

## 🚀 Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd retail-msme-erp
```

### 2. Install Dependencies
```bash
npm install
```

### 3. Environment Configuration
Create a `.env` file in the root directory (optional):
```env
PORT=3000
JWT_SECRET=your-super-secret-key-change-in-production
NODE_ENV=development
```

### 4. Start the Application
```bash
# Development mode with auto-reload
npm run dev

# Production mode
npm start
```

### 5. Access the Application
Open your browser and navigate to:
```
http://localhost:3000
```

## 🔐 Default Login Credentials

- **Username:** `admin`
- **Password:** `admin123`

**⚠️ Important:** Change these credentials immediately after first login in production!

## 📊 Database Schema

The system automatically creates the following tables:

### Core Tables
- `users` - User accounts and authentication
- `chart_of_accounts` - Financial accounts structure
- `journal_entries` - Financial transactions
- `journal_entry_lines` - Transaction line items

### Business Tables
- `customers` - Customer information
- `suppliers` - Supplier information
- `products` - Inventory items
- `sales_orders` - Sales transactions
- `purchase_orders` - Purchase transactions
- `invoices` - Customer billing
- `payments` - Financial transactions

## 🎯 Usage Guide

### Getting Started

1. **Login** with the default admin credentials
2. **Navigate** through different modules using the sidebar
3. **Start** by setting up your Chart of Accounts
4. **Add** your first customers and suppliers
5. **Create** product catalog
6. **Begin** processing sales and purchases

### Key Workflows

#### Setting Up Chart of Accounts
1. Go to **Accounting** → **Chart of Accounts**
2. Click **Add Account**
3. Enter account code, name, and type
4. Save and repeat for all necessary accounts

#### Managing Inventory
1. Navigate to **Inventory** module
2. Click **Add Product**
3. Fill in product details (code, name, category, prices)
4. Set initial stock levels and reorder points

#### Customer Management
1. Go to **Customers** module
2. Click **Add Customer**
3. Enter customer details and credit limits
4. Track customer balances and history

#### Sales Processing
1. Navigate to **Sales** module
2. Click **New Sales Order**
3. Select customer and add products
4. Process payment and update inventory

## 🔧 Configuration

### System Settings
- Company information
- Default currency
- Date formats
- Time zones
- Tax settings

### User Preferences
- Dashboard layout
- Notification settings
- Language preferences
- Theme customization

## 📱 Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🔒 Security Features

- **JWT Authentication** - Secure token-based auth
- **Password Hashing** - bcrypt encryption
- **Rate Limiting** - API abuse prevention
- **CORS Protection** - Cross-origin security
- **Helmet Security** - HTTP header protection
- **Input Validation** - Data sanitization
- **SQL Injection Protection** - Parameterized queries

## 📈 Performance Features

- **Database Indexing** - Optimized queries
- **Connection Pooling** - Efficient database connections
- **Caching Ready** - Redis integration ready
- **Compression** - Gzip compression support
- **Static File Serving** - Optimized asset delivery

## 🚀 Deployment

### Production Considerations

1. **Environment Variables**
   - Set secure JWT secret
   - Configure production database
   - Set proper NODE_ENV

2. **Database**
   - Consider PostgreSQL for production
   - Implement backup strategies
   - Set up monitoring

3. **Security**
   - Use HTTPS
   - Implement rate limiting
   - Set up firewall rules
   - Regular security updates

4. **Monitoring**
   - Application performance monitoring
   - Error tracking
   - Log management
   - Health checks

## 🔄 API Endpoints

### Authentication
- `POST /api/auth/login` - User login

### Dashboard
- `GET /api/dashboard/stats` - Dashboard statistics

### Accounting
- `GET /api/accounts` - List accounts
- `POST /api/accounts` - Create account

### Customers
- `GET /api/customers` - List customers
- `POST /api/customers` - Create customer

### Products
- `GET /api/products` - List products
- `POST /api/products` - Create product

### Sales Orders
- `GET /api/sales-orders` - List sales orders
- `POST /api/sales-orders` - Create sales order

## 🧪 Testing

Run tests to ensure everything works correctly:

```bash
# Run all tests
npm test

# Run specific test suites
npm run test:unit
npm run test:integration
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the GitHub repository
- Check the documentation
- Review the code examples

## 🔮 Roadmap

### Upcoming Features
- **Multi-tenant Support** - Multiple business support
- **Advanced Reporting** - Custom report builder
- **Mobile App** - Native mobile application
- **API Integration** - Third-party service connections
- **Advanced Analytics** - Machine learning insights
- **Multi-language** - Internationalization support
- **Cloud Deployment** - Docker and cloud ready
- **Backup & Recovery** - Automated backup systems

### Version History
- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Enhanced reporting and analytics
- **v1.2.0** - Advanced inventory management
- **v2.0.0** - Multi-tenant architecture

## 🙏 Acknowledgments

- Built with modern web technologies
- Inspired by enterprise ERP solutions
- Designed for MSME business needs
- Community-driven development

---

**Built with ❤️ for MSMEs worldwide**

*This ERP system provides a solid foundation for retail businesses to manage their operations efficiently and grow their business.*