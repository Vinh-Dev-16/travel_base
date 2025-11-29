<?php

namespace Vinhdev\Travel\Contracts\Lib;

use Illuminate\Redis\Connections\Connection as RedisConnection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RedisLib implements RedisLibContract
{
    protected RedisConnection $redis;
    protected int $currentDb = 0;

    // Lưu trữ các connection riêng biệt cho từng database
    private static array $connections = [];

    /**
     * Khởi tạo Redis connection
     *
     * @param  int  $db  Database number (0-15)
     */
    public function __construct($db = 0)
    {
        $connectionName = $this->getConnectionName($db);

        // Sử dụng connection pool để tránh tạo quá nhiều connections
        if (!isset(self::$connections[$db])) {
            self::$connections[$db] = Redis::connection($connectionName);
        }

        $this->redis     = self::$connections[$db];
        $this->currentDb = $db;
    }

    /**
     * Lấy tên connection dựa trên database number
     *
     * @param  int  $db  Database number
     * @return string Tên connection name
     */
    private function getConnectionName($db): string
    {
        return match ($db) {
            1       => 'cache',          // DB_CACHE
            2       => 'queue',          // DB_OTP và QUEUE
            5       => 'authentication', // DB_AUTHENTICATION
            10      => 'background',     // DB_BACKGROUND
            default => 'default'         // Các database khác
        };
    }

    /**
     * Set giá trị vào Redis với TTL
     *
     * @param  string  $key  Key để lưu
     * @param  mixed  $value  Giá trị cần lưu (string, array, object...)
     * @param  int  $ttl  Time to live (seconds), mặc định 3600 giây (1 giờ)
     * @return bool True nếu set thành công
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        // Luôn đặt TTL theo contract; nếu muốn không TTL, truyền ttl lớn hoặc sửa contract
        $result = $this->redis->setex($key, $ttl, $value);
        return (bool)$result;
    }

    /**
     * Lấy giá trị từ Redis theo key
     *
     * @param  string  $key  Key cần lấy
     * @return mixed|null Giá trị của key hoặc null nếu không tồn tại
     */
    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    /**
     * Xóa một key khỏi Redis
     *
     * @param  string  $key  Key cần xóa
     * @return bool True nếu xóa thành công
     */
    public function delete(string $key): bool
    {
        return (int)$this->redis->del($key) > 0;
    }

    /**
     * Thêm phần tử vào cuối queue (FIFO)
     *
     * @param  string  $queue  Tên queue
     * @param  mixed  $value  Giá trị cần thêm
     * @return int Số lượng phần tử trong queue sau khi thêm
     */
    public function enqueue(string $queue, $value): int
    {
        return $this->redis->rpush($queue, $value);
    }

    /**
     * Lấy và xóa phần tử đầu tiên của queue (FIFO)
     *
     * @param  string  $queue  Tên queue
     * @return mixed|null Giá trị phần tử đầu tiên hoặc null nếu queue rỗng
     */
    public function dequeue(string $queue): mixed
    {
        return $this->redis->lpop($queue);
    }

    /**
     * Thêm phần tử vào đầu danh sách (LIFO)
     *
     * @param  string  $queue  Tên list
     * @param  mixed  $value  Giá trị cần thêm
     * @return int Số lượng phần tử trong list sau khi thêm
     */
    public function addToListStart(string $queue, $value): int
    {
        return $this->redis->lpush($queue, $value);
    }

    /**
     * Giới hạn kích thước của list, giữ lại maxSize phần tử đầu tiên
     *
     * @param  string  $queue  Tên list
     * @param  int  $maxSize  Số lượng phần tử tối đa muốn giữ lại
     * @return void
     */
    public function limitListSize(string $queue, int $maxSize): void
    {
        $this->redis->ltrim($queue, 0, $maxSize - 1);
    }

    /**
     * Lấy các phần tử trong list theo range
     *
     * @param  string  $queue  Tên list
     * @param  int  $start  Vị trí bắt đầu (0-indexed)
     * @param  int  $end  Vị trí kết thúc (-1 là lấy đến cuối)
     * @return array Mảng các phần tử
     */
    public function getList(string $queue, int $start = 0, int $end = -1): array
    {
        return $this->redis->lrange($queue, $start, $end);
    }

    /**
     * Tăng điểm số của member trong sorted set
     *
     * @param  string  $key  Tên sorted set
     * @param  string  $member  Tên member cần tăng điểm
     * @param  float  $increment  Số điểm cần tăng (mặc định 1.0)
     * @return void
     */
    public function incrementSortedSetScore(string $key, string $member, float $increment = 1.0): void
    {
        $this->redis->zincrby($key, $increment, $member);
    }

    /**
     * Lấy phần tử tại vị trí index trong list
     *
     * @param  string  $queue  Tên list
     * @param  int  $index  Vị trí cần lấy (0-indexed)
     * @return mixed|null Giá trị tại vị trí index hoặc null nếu không tồn tại
     */
    public function getListItemAt(string $queue, int $index): mixed
    {
        return $this->redis->lindex($queue, $index);
    }

    /**
     * Kiểm tra key có tồn tại trong Redis không
     *
     * @param  string  $key  Key cần kiểm tra
     * @return bool True nếu key tồn tại
     */
    public function exists(string $key): bool
    {
        return (bool)$this->redis->exists($key);
    }

    /**
     * Push data vào background queue với metadata
     *
     * @param  string  $key  Queue key
     * @param  string  $value  Dữ liệu cần push
     * @param  string  $dataType  Loại dữ liệu
     * @return void
     */
    public function pushToBackground(string $key, string $value, string $dataType): void
    {
        $data = [
            'id'       => Str::uuid()->toString(),
            'dataType' => $dataType,
            'data'     => $value,
        ];

        $this->redis->rpush($key, json_encode($data));
    }

    /**
     * Tìm tất cả các key khớp với pattern (sử dụng SCAN)
     *
     * @param  string  $key  Pattern để tìm kiếm (ví dụ: "user:*")
     * @return array Mảng các key tìm được
     */
    public function keys(string $key): array
    {
        $cursor = 0;
        $keys   = [];

        do {
            $result = $this->redis->scan($cursor, 'MATCH', $key);
            $cursor = $result[0];
            $keys   = array_merge($keys, $result[1]);
        } while ($cursor != 0);

        return $keys;
    }

    /**
     * Xóa tất cả các key khớp với pattern (sử dụng SCAN)
     *
     * @param  string  $pattern  Pattern để xóa (ví dụ: "cache:*")
     * @return void
     */
    public function deleteByPattern(string $pattern): void
    {
        $cursor = '0';
        do {
            [$cursor, $keys] = $this->redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 1000]);

            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        } while ($cursor !== '0');
    }

    /**
     * Set giá trị cho một field trong hash
     *
     * @param  string  $key  Hash key
     * @param  string  $field  Tên field
     * @param  mixed  $value  Giá trị cần set
     * @return int 1 nếu field mới, 0 nếu field đã tồn tại và được update
     */
    public function hSet(string $key, string $field, $value): int
    {
        return (int)$this->redis->hset($key, $field, $value);
    }

    /**
     * Lấy giá trị của một field trong hash
     *
     * @param  string  $key  Hash key
     * @param  string  $field  Tên field cần lấy
     * @return mixed|null Giá trị của field hoặc null nếu không tồn tại
     */
    public function hGet(string $key, string $field): mixed
    {
        return $this->redis->hget($key, $field);
    }

    /**
     * Lấy tất cả field và value trong hash
     *
     * @param  string  $key  Hash key
     * @return array Mảng associative [field => value]
     */
    public function hGetAll(string $key): array
    {
        return $this->redis->hgetall($key) ?: [];
    }

    /**
     * Xóa một hoặc nhiều field trong hash
     *
     * @param  string  $key  Hash key
     * @param  string  ...$fields  Các field cần xóa
     * @return int Số lượng field đã xóa
     */
    public function hDel(string $key, string ...$fields): int
    {
        return (int)$this->redis->hdel($key, ...$fields);
    }

    /**
     * Kiểm tra field có tồn tại trong hash không
     *
     * @param  string  $key  Hash key
     * @param  string  $field  Tên field cần kiểm tra
     * @return bool True nếu field tồn tại
     */
    public function hExists(string $key, string $field): bool
    {
        return (bool)$this->redis->hexists($key, $field);
    }

    /**
     * Set nhiều field trong hash cùng lúc
     *
     * @param  string  $key  Hash key
     * @param  array  $data  Mảng associative [field => value]
     * @return bool True nếu set thành công
     */
    public function hMSet(string $key, array $data): bool
    {
        return (bool)$this->redis->hmset($key, $data);
    }

    /**
     * Acquire distributed lock với token (dùng cho xử lý đồng thời)
     * Lock tự động expire sau TTL để tránh deadlock
     * Sử dụng Redis SET NX EX để đảm bảo atomic operation
     *
     * @param  string  $key  Lock key (ví dụ: "user:123" sẽ tạo lock "lock:user:123")
     * @param  int  $ttl  Time to live (seconds) - tự động unlock sau thời gian này, mặc định 10 giây
     * @return string|null Token (UUID) nếu lock thành công, null nếu key đã bị lock
     * @throws InvalidArgumentException Nếu TTL <= 0
     */
    public function lock(string $key, int $ttl = 10): ?string
    {
        if ($ttl <= 0) {
            throw new InvalidArgumentException('TTL must be positive');
        }
        $lockKey = "lock:{$key}";
        $token   = Str::uuid()->toString();
        $result  = $this->redis->set($lockKey, $token, 'EX', $ttl, 'NX');

        return $result ? $token : null;
    }

    /**
     * Release lock với token - chỉ unlock được nếu token đúng
     * Sử dụng Lua script để đảm bảo atomic operation (check + delete)
     *
     * @param  string  $key  Lock key
     * @param  string  $token  Token nhận được khi lock
     * @return bool True nếu unlock thành công, False nếu token không match hoặc lock đã hết hạn
     */
    public function unlock(string $key, string $token): bool
    {
        $lockKey = "lock:{$key}";

        // Lua script để đảm bảo atomic operation
        // Chỉ delete nếu token match
        $script = "
            if redis.call('get', KEYS[1]) == ARGV[1] then
                return redis.call('del', KEYS[1])
            else
                return 0
            end
        ";

        $result = $this->redis->eval($script, 1, $lockKey, $token);

        return (bool)$result;
    }

    /**
     * Kiểm tra xem key có đang bị lock không
     *
     * @param  string  $key  Lock key
     * @return bool True nếu key đang bị lock bởi bất kỳ process nào
     */
    public function isLocked(string $key): bool
    {
        $lockKey = "lock:{$key}";
        return $this->exists($lockKey);
    }

    /**
     * Gia hạn thời gian lock - dùng khi xử lý lâu hơn dự kiến
     * Sử dụng Lua script để đảm bảo atomic operation (check token + extend TTL)
     * TTL mới = TTL hiện tại + additionalTtl
     *
     * @param  string  $key  Lock key
     * @param  string  $token  Token nhận được khi lock
     * @param  int  $additionalTtl  Thời gian thêm (seconds)
     * @return bool True nếu extend thành công, False nếu token không match hoặc lock đã hết hạn
     */
    public function extendLock(string $key, string $token, int $additionalTtl): bool
    {
        $lockKey = "lock:{$key}";

        // Lua script để đảm bảo atomic operation
        // Chỉ extend nếu token match
        $script = "
            if redis.call('get', KEYS[1]) == ARGV[1] then
                local current_ttl = redis.call('ttl', KEYS[1])
                if current_ttl > 0 then
                    return redis.call('expire', KEYS[1], current_ttl + ARGV[2])
                else
                    return 0
                end
            else
                return 0
            end
        ";

        $result = $this->redis->eval($script, 1, $lockKey, $token, $additionalTtl);

        return (bool)$result;
    }

    /**
     * Thực thi Lua script trên Redis
     *
     * @param  string  $script  Lua script cần thực thi
     * @param  int  $numberOfKeys  Số lượng keys (sẽ được truyền vào KEYS[])
     * @param  mixed  ...$arguments  Các tham số còn lại (sẽ được truyền vào ARGV[])
     * @return mixed Kết quả trả về từ Lua script
     */
    public function eval(string $script, int $numberOfKeys, ...$arguments): mixed
    {
        return $this->redis->eval($script, $numberOfKeys, ...$arguments);
    }

    /**
     * Xóa một phần tử khỏi set
     *
     * @param  string  $key  Set key
     * @param  string  $member  Phần tử cần xóa
     * @return int Số lượng phần tử đã xóa (0 hoặc 1)
     */
    public function srem(string $key, string $member): int
    {
        return (int)$this->redis->srem($key, $member);
    }

    /**
     * Thêm một hoặc nhiều phần tử vào set
     *
     * @param  string  $key  Set key
     * @param  string  ...$members  Các phần tử cần thêm
     * @return int Số lượng phần tử mới được thêm vào
     */
    public function sadd(string $key, string ...$members): int
    {
        return (int)$this->redis->sadd($key, ...$members);
    }

    /**
     * Lấy tất cả các phần tử trong set
     *
     * @param  string  $key  Set key
     * @return array Mảng các phần tử trong set
     */
    public function smembers(string $key): array
    {
        return $this->redis->smembers($key);
    }

    /**
     * Tăng giá trị của key lên 1
     *
     * @param  string  $key  Key cần tăng giá trị
     * @return int Giá trị mới sau khi tăng
     */
    public function incr(string $key): int
    {
        return (int)$this->redis->incr($key);
    }
}
