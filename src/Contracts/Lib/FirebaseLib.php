<?php

namespace Vinhdev\Travel\Contracts\Lib;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Storage;
use Vinhdev\Travel\Contracts\DataMappers\NotificationData;


class FirebaseLib
{
    private Database $database;
    private Messaging $messaging;
    private Storage $storage;
    private Factory $factory;
    private Auth $auth;

    public function __construct(array $config = null)
    {
        // Nếu không có config được truyền vào, thử lấy từ Laravel config
        if ($config === null) {
            $config = $this->getLaravelConfig();
        }
        
        // Nếu vẫn không có config, sử dụng default
        if ($config === null) {
            $config = [
                'credential_path' => getcwd() . '/storage/app/firebase-service-account.json',
                'database_uri' => 'https://your-project-id-default-rtdb.firebaseio.com/'
            ];
        }
        
        $factory = (new Factory)
            ->withServiceAccount($config['credential_path'])
            ->withDatabaseUri($config['database_uri']);
            
        $this->messaging = $factory->createMessaging();
        $this->database  = $factory->createDatabase();
        $this->storage   = $factory->createStorage();
        $this->auth      = $factory->createAuth();
    }

    /**
     * Lấy config từ Laravel nếu có
     */
    private function getLaravelConfig(): ?array
    {
        // Thử các cách khác nhau để lấy config từ Laravel
        if (function_exists('config')) {
            return config('travel.firebase');
        }
        
        if (class_exists('Illuminate\Support\Facades\Config')) {
            return \Illuminate\Support\Facades\Config::get('travel.firebase');
        }
        
        if (class_exists('Illuminate\Container\Container')) {
            $container = \Illuminate\Container\Container::getInstance();
            if ($container->bound('config')) {
                $config = $container->make('config');
                return $config->get('travel.firebase');
            }
        }
        
        return null;
    }

    public function uploadFile($filePath, $fileName): array
    {
        $bucket = $this->storage->getBucket();
        $file   = fopen($filePath, 'r');

        $object = $bucket->upload($file, [
            'name' => 'Travel/'.$fileName,
        ]);

        return [];
    }

    public function getFileUrl($fileName): string
    {
        $bucket = $this->storage->getBucket();

        $object = $bucket->object($fileName);
        if ($object->exists()) {
            return $object->signedUrl(new \DateTime('+1 hour'));
        }

        return "File does not exist.";
    }

    public function getAccessToken(string $email, string $password): ?string
    {
        try {
            $signInResult = $this->auth->signInWithEmailAndPassword($email, $password);
            return $signInResult->idToken();
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            echo 'Authentication failed: '.$e->getMessage();
            return null;
        }
    }

    /**
     * @throws DatabaseException
     */
    public function saveComment($commentData): void
    {
        $this->database
            ->getReference('comments')
            ->push($commentData);
    }

    /**
     * @throws DatabaseException
     */
    public function getComments(): array
    {
        return $this->database
            ->getReference('comments')
            ->getValue();
    }

    /**
     * @throws DatabaseException
     */
    public function deleteComments(): void
    {
        $this->database
            ->getReference('comments')
            ->remove();
    }


    public function createMessaging(): Messaging|\Kreait\Firebase\Contract\Messaging
    {
        return $this->messaging;
    }

    /**
     * @throws DatabaseException
     */
    public function createData(string $table, array $data): void
    {
        $this->database->getReference($table)->push($data);
    }

    /**
     * @throws DatabaseException
     */
    public function deleteData(string $table): void
    {
        $this->database->getReference($table)->remove();
    }


    /**
     * @param  string  $token
     * @param  NotificationData  $data
     * @return void
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function sendNotification(string $token, NotificationData $data): void
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification([
                'title' => $data->getTitle(),
                'body'  => $data->getBody(),
            ]);

        $this->messaging->send($message);
    }
}