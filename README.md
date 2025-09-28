# Travel Package

Một package Laravel cung cấp các tính năng cơ bản cho ứng dụng du lịch, bao gồm Firebase integration, MongoDB support, Redis caching và các helper functions hữu ích.

## 📋 Yêu cầu hệ thống

- PHP >= 8.1
- Laravel >= 10.0 (tùy chọn - package cũng hoạt động standalone)
- MongoDB extension (nếu sử dụng MongoDB)
- Redis (nếu sử dụng Redis)

## 🚀 Cài đặt

### 1. Cài đặt Package

```bash
composer require vinhdev/travel
```

### 2. Publish Config (Laravel)

```bash
php artisan vendor:publish --tag=travel-config
```

### 3. Cấu hình Environment

Thêm vào file `.env`:

```env
# Firebase Configuration
FIREBASE_DATABASE_URI=https://your-project-id-default-rtdb.firebaseio.com/

# MongoDB Configuration
MONGODB_CONNECTION=mongodb://localhost:27017
MONGODB_DATABASE=travel_db

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DATABASE=0
```

### 4. Cài đặt Firebase Service Account

1. Tải file service account JSON từ Firebase Console
2. Đặt file vào `storage/app/firebase-service-account.json`
3. Hoặc cập nhật đường dẫn trong `config/travel.php`

## 📚 Sử dụng

### BaseController

```php
use Vinhdev\Travel\Contracts\Controllers\BaseController;

$controller = new BaseController();

// Response JSON với message
$response = $controller->responseJson(200, 'Thành công');

// Response JSON với data
$response = $controller->responseJsonData(200, [
    'users' => $users,
    'total' => 100
]);
```

### BaseModel (MongoDB)

```php
use Vinhdev\Travel\Contracts\Models\BaseModel;

class User extends BaseModel
{
    protected $collection = 'users';
    
    // Model sẽ tự động:
    // - Tạo _id mới khi tạo record
    // - Set created_at, updated_at timestamps
    // - Set is_deleted = 0 (ACTIVE)
}
```

### BaseRequest

```php
use Vinhdev\Travel\Contracts\Requests\BaseRequest;

class CreateUserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users'
        ];
    }
    
    // Sử dụng getDTO() để lấy thông tin user hiện tại
    public function getDTO(): GetUserInformationDTOInterface
    {
        return parent::getDTO();
    }
}
```

### FirebaseLib

```php
use Vinhdev\Travel\Contracts\Lib\FirebaseLib;
use Vinhdev\Travel\Contracts\DataMappers\NotificationData;

// Khởi tạo (tự động lấy config từ Laravel)
$firebase = new FirebaseLib();

// Hoặc truyền config trực tiếp
$firebase = new FirebaseLib([
    'credential_path' => '/path/to/firebase.json',
    'database_uri' => 'https://your-project.firebaseio.com/'
]);

// Upload file
$result = $firebase->uploadFile('/path/to/file.jpg', 'image.jpg');

// Lấy URL file
$url = $firebase->getFileUrl('Travel/image.jpg');

// Gửi notification
$notification = new NotificationData('Tiêu đề', 'Nội dung thông báo');
$firebase->sendNotification($token, $notification);

// Lưu dữ liệu vào Firebase Database
$firebase->createData('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Lấy dữ liệu
$comments = $firebase->getComments();

// Xóa dữ liệu
$firebase->deleteData('users');
```

### RedisLib

```php
use Vinhdev\Travel\Contracts\Lib\RedisLib;

$redis = new RedisLib();

// Set cache
$redis->set('user:1', json_encode($userData), 3600);

// Get cache
$userData = $redis->get('user:1');

// Delete cache
$redis->delete('user:1');
```

## 🔧 Cấu hình

### Config File (config/travel.php)

```php
return [
    'firebase' => [
        'credential_path' => storage_path('app/firebase-service-account.json'),
        'database_uri' => env('FIREBASE_DATABASE_URI'),
    ],
    
    'mongodb' => [
        'connection' => env('MONGODB_CONNECTION', 'mongodb://localhost:27017'),
        'database' => env('MONGODB_DATABASE', 'travel_db'),
    ],
    
    'redis' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD'),
        'database' => env('REDIS_DATABASE', 0),
    ],
];
```

## 📦 Các Class có sẵn

### Controllers
- `BaseController` - Controller cơ bản với response helpers

### Models
- `BaseModel` - Model cơ bản cho MongoDB với soft delete

### Requests
- `BaseRequest` - Request cơ bản với validation và DTO helpers

### Libraries
- `FirebaseLib` - Firebase integration (Auth, Database, Storage, Messaging)
- `RedisLib` - Redis caching helpers

### DTOs
- `NotificationData` - Data class cho Firebase notifications
- `UserInformationDTO` - Data class cho thông tin user

### Enums
- `SoftDelete` - Enum cho trạng thái soft delete

### Traits
- `GetUserInformationDTOTrait` - Trait cho user information
- `HasPermissionTrait` - Trait cho permission checking
- `IndexPaginateDTOTrait` - Trait cho pagination

## 🛠️ Development

### Cài đặt dependencies

```bash
composer install
```

### Chạy tests

```bash
composer test
```

## 📝 Changelog

### v1.0.0
- BaseController với JSON response helpers
- BaseModel với MongoDB support và soft delete
- BaseRequest với validation và DTO integration
- FirebaseLib với đầy đủ tính năng Firebase
- RedisLib với caching helpers
- Service Provider cho Laravel integration
- Config system linh hoạt

## 🤝 Contributing

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Tạo Pull Request

## 📄 License

Package này được phát hành dưới [MIT License](LICENSE).

## 👨‍💻 Author

**Vinh Dev 16**
- Email: vinhdev@example.com
- GitHub: [@vinhdev16](https://github.com/vinhdev16)

## 🙏 Acknowledgments

- Laravel Framework
- Firebase PHP SDK
- MongoDB Laravel Package
- Redis PHP Extension
