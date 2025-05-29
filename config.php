<?php
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'SecureAdmin123!');

$mongodb_uri = getenv('MONGODB_URI');
if (!$mongodb_uri) {
    error_log('MONGODB_URI environment variable not set');
    $mongodb_uri = 'mongodb+srv://newgen:Classicman2024@cluster3.h0ccne.mongodb.net/?retryWrites=true&w=majority&appName=Cluster3';
}

try {
    require_once 'vendor/autoload.php';
    
    $client = new MongoDB\Client($mongodb_uri, [
        'connectTimeoutMS' => 5000,
        'serverSelectionTimeoutMS' => 5000
    ]);
    
    $db = $client->selectDatabase('verification_system');
    
    $db->command(['ping' => 1]);
    
} catch (Exception $e) {
    error_log('MongoDB connection failed: ' . $e->getMessage());
    
    $db = new class {
        public $emails;
        
        public function __construct() {
            $this->emails = new class {
                private $data = [];
                
                public function countDocuments($filter = []) {
                    return count($this->data);
                }
                
                public function find($filter = [], $options = []) {
                    return new class($this->data) {
                        private $data;
                        
                        public function __construct($data) {
                            $this->data = $data;
                        }
                        
                        public function toArray() {
                            return $this->data;
                        }
                    };
                }
                
                public function findOne($filter) {
                    return null;
                }
                
                public function insertOne($doc) {
                    $this->data[] = $doc;
                    return new class {
                        public function getInsertedCount() {
                            return 1;
                        }
                    };
                }
                
                public function deleteOne($filter) {
                    return new class {
                        public function getDeletedCount() {
                            return 1;
                        }
                    };
                }
            };
        }
    };
}

define('DATABASE_NAME', 'verification_system');
?>