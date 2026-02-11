# 跨境电商商城

基于 WordPress + WooCommerce 构建的跨境电商商城系统，包含用户端商城和管理后台。

## How to Run

### 前置要求

- Docker 20.10+
- Docker Compose 2.0+

### 启动项目

```bash
# 克隆项目
git clone <repository-url>
cd 950

# 复制环境变量配置文件并修改
cp .env.example .env
# 编辑 .env 文件，设置安全的密码和密钥

# 构建并启动所有服务
docker-compose up --build -d

# 查看运行状态
docker-compose ps

# 查看日志
docker-compose logs -f
```

### 安全配置

⚠️ 生产环境部署前，请务必：

1. 复制 `.env.example` 为 `.env` 并修改所有默认密码
2. 生成新的 WordPress 安全密钥（访问 https://api.wordpress.org/secret-key/1.1/salt/）
3. 设置强密码（至少12位，包含大小写字母、数字和特殊字符）
4. 限制 CORS 允许的域名（修改 `backend/api/products.php` 中的 `$allowedOrigins`）

### 停止项目

```bash
# 停止服务
docker-compose down

# 停止并删除数据卷（慎用，会删除数据库数据）
docker-compose down -v
```

## Services

| 服务 | 端口 | 说明 |
|------|------|------|
| backend | 9080 | WordPress 后端 API |
| frontend-admin | 9081 | 管理后台前端 |
| frontend-user | 9082 | 用户端商城前端 |
| mysql | 3306 | MySQL 数据库（内部） |

### 访问地址

- **用户端商城**: http://localhost:9082
- **管理后台**: http://localhost:9081
- **WordPress 后台**: http://localhost:9080/wp-admin

## 测试账号

### 管理后台 / WordPress 后台

| 用户名 | 密码 |
|--------|------|
| admin | admin123 |

### 用户端商城

可使用上述管理员账号登录，或在用户端注册新账号。

## 题目内容
基于 wordprdess 框架做一个跨境电商商城 包含前端商城和后台管理系统


### Docker 规范要求

1. ✅ 命名规范：backend（后端）、frontend-admin（管理后台）、frontend-user（用户端）
2. ✅ 每个子项目包含 Dockerfile，支持 ARM 和 X86 架构
3. ✅ 根目录包含 docker-compose.yml、.gitignore、README.md
4. ✅ 支持 `docker-compose up --build -d` 运行
5. ✅ 前端端口映射：8081（管理后台）、8082（用户端）
6. ✅ .gitignore 包含所有子项目需要忽略的文件
7. ✅ README.md 包含 How to Run、Services、测试账号、题目内容

---

## 项目结构

```
950/
├── backend/                    # WordPress 后端
│   ├── Dockerfile
│   ├── wp-config-docker.php    # WordPress 配置
│   ├── uploads.ini             # PHP 上传配置
│   ├── docker-entrypoint-init.sh
│   ├── plugins/                # 自定义插件
│   │   └── cross-border-commerce/  # 跨境电商增强插件
│   └── themes/                 # 自定义主题
│
├── frontend-admin/             # 管理后台前端 (Vue 3 + Element Plus)
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── package.json
│   ├── vite.config.js
│   └── src/
│       ├── views/              # 页面组件
│       ├── components/         # 公共组件
│       ├── stores/             # 状态管理
│       └── utils/              # 工具函数
│
├── frontend-user/              # 用户端商城前端 (Vue 3)
│   ├── Dockerfile
│   ├── nginx.conf
│   ├── package.json
│   ├── vite.config.js
│   └── src/
│       ├── views/              # 页面组件
│       ├── components/         # 公共组件
│       ├── stores/             # 状态管理
│       └── utils/              # 工具函数
│
├── docker-compose.yml          # Docker 编排配置
├── .gitignore                  # Git 忽略文件
└── README.md                   # 项目说明文档
```

## 技术栈

### 后端

- **WordPress 6.4** - 内容管理系统
- **WooCommerce** - 电商插件
- **PHP 8.2** - 后端语言
- **MySQL 8.0** - 数据库

### 前端

- **Vue 3** - 前端框架
- **Vite 5** - 构建工具
- **Pinia** - 状态管理
- **Vue Router 4** - 路由管理
- **Element Plus** - UI 组件库（管理后台）
- **Axios** - HTTP 客户端
- **ECharts** - 图表库（管理后台）

### 基础设施

- **Docker** - 容器化
- **Nginx** - Web 服务器（前端）
- **Apache** - Web 服务器（WordPress）

## 功能特性

### 用户端商城

- 🏠 首页展示（Banner、热门分类、热销商品）
- 🛍️ 商品列表（分类筛选、价格筛选、排序）
- 📦 商品详情（图片画廊、规格参数、加入购物车）
- 🛒 购物车管理
- 💳 结算流程
- 👤 用户中心（个人信息、收货地址、我的订单）
- 🔐 登录注册

### 管理后台

- 📊 仪表盘（统计数据、销售趋势、订单分布）
- 📦 商品管理（列表、添加、编辑、删除）
- 📋 订单管理（列表、详情、状态更新）
- 👥 用户管理（列表、编辑、状态切换）
- ⚙️ 系统设置（基本设置、支付设置、物流设置、货币设置）

### 跨境电商特性

- 💱 多货币支持
- 🌍 国际物流计算
- 📋 关税计算
- 🔒 CORS 跨域支持

### 安全特性

- 🛡️ CSRF 保护
- 🔐 XSS 防护（输入清理和输出转义）
- ⏱️ 速率限制
- 📝 安全事件日志
- 🔑 环境变量管理敏感信息
- 🔒 安全响应头（CSP、X-Frame-Options 等）

## API 接口

### 商品接口

- `GET /wp-json/cbc/v1/products` - 获取商品列表
- `GET /wp-json/cbc/v1/products/{id}` - 获取商品详情
- `GET /wp-json/cbc/v1/categories` - 获取分类列表

### 订单接口

- `GET /wp-json/cbc/v1/orders` - 获取订单列表
- `POST /wp-json/cbc/v1/orders` - 创建订单

### 工具接口

- `GET /wp-json/cbc/v1/currency/convert` - 货币转换
- `POST /wp-json/cbc/v1/shipping/calculate` - 运费计算

### 认证接口

- `POST /wp-json/jwt-auth/v1/token` - 用户登录获取 Token

## 开发说明

### 本地开发

```bash
# 后端开发（进入容器）
docker-compose exec backend bash

# 前端开发
cd frontend-admin  # 或 frontend-user
npm install
npm run dev
```

### 重新构建

```bash
# 重新构建单个服务
docker-compose build backend
docker-compose build frontend-admin
docker-compose build frontend-user

# 重新构建所有服务
docker-compose build

# 重新构建并启动
docker-compose up --build -d
```

## License

MIT License
