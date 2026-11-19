<?php
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Exam.php';
require_once __DIR__ . '/Question.php';
require_once __DIR__ . '/Payment.php';
require_once __DIR__ . '/Paystack.php';
require_once __DIR__ . '/Attempt.php';
require_once __DIR__ . '/Dashboard.php';
require_once __DIR__ . '/Payout.php';
require_once __DIR__ . '/Complaint.php'; 
require_once __DIR__ . '/Result.php';

class ApiHandler {

    private const ENTITY_CLASS = [
        'auth'      => 'Auth',
        'users'     => 'User',
        'exams'     => 'Exam',       
        'questions' => 'Question',
        'payments'  => 'Payment',
        'attempts'  => 'Attempt',
        'dashboard' => 'Dashboard',
        'payouts'   => 'Payout',        
        'complaints'=> 'Complaint',
        'results'   => 'Result', 
    ];

    private const LIST_METHOD = [
        'exams'     => 'getAllWithStats',       
        'questions' => 'getByExam',
        'dashboard' => 'getAdminStats',
        'payouts'   => 'getPendingPayouts',
        'complaints'=> 'getAll',
        'users'     => 'getAllUsers',
        'results'   => 'getExamResults', 
        'payments'  => 'getAllPayments',
    ];

    private const GET_METHOD = [
        'exams'     => 'getById',
        'questions' => 'getQuestionById',
    ];

    private const CREATE_METHOD = [
        'exams'     => 'create',       
        'questions' => 'create',       
        'payments'  => 'initializeExamEntry',
        'attempts'  => 'start',  
        'complaints'=> 'create',
    ];

    private const UPDATE_METHOD = [
        'exams'     => 'updateExam', 
        'questions' => 'updateQuestion',
    ];

    private const DELETE_METHOD = [
        'exams'     => 'softDelete', 
        'questions' => 'deleteQuestion', 
    ];

    private const POST_ACTION_METHODS = [
        'pay'           => ['entity' => 'payouts',    'method' => 'pay',          'needs_user' => false],
        'resolve'       => ['entity' => 'complaints', 'method' => 'resolve',      'needs_user' => true],
        'submit'        => ['entity' => 'attempts',   'method' => 'submit',       'needs_user' => true],
        'verify_manual' => ['entity' => 'payments',   'method' => 'manualVerify', 'needs_user' => false],
        'create_manual' => ['entity' => 'payments',   'method' => 'manualCreate', 'needs_user' => false],
        'toggle_status' => ['entity' => 'users',      'method' => 'toggleStatus', 'needs_user' => false],
        'verify'        => ['entity' => 'payments',   'method' => 'verifyTransaction', 'needs_user' => false],
    ];

    private const AUTH_TASKS = [
        'register'          => 'register',
        'login'             => 'login',
        'logout'            => 'logout',
        'request_reset'     => 'requestPasswordReset',
        'verify_reset_code' => 'verifyResetCode',
        'reset_password'    => 'resetPassword',
        'verify_email'      => 'verifyEmail',
    ];

    private const PUBLIC_ENTITIES = ['auth'];

    private string $entity;
    private string $action;
    private $id;
    private array $input;
    private string $requestMethod;

    public function __construct() {
        $this->action        = $_GET['action'] ?? '';
        $this->id            = $_GET['id'] ?? 0;
        $this->entity        = $_GET['entity'] ?? '';
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $raw = file_get_contents('php://input');
        $decoded = $raw ? json_decode($raw, true) : [];
        $this->input = is_array($decoded) ? $decoded : [];

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: ' . Config::corsOrigin());
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
    }

    public function handleRequest(): void {
        if ($this->requestMethod === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        try {
            $this->authorize();

            match ($this->requestMethod) {
                'GET'    => $this->handleGet(),
                'POST'   => $this->handlePost(),
                'PUT'    => $this->handlePut(),
                'DELETE' => $this->handleDelete(),
                default  => $this->respond(false, null, 'Method not allowed', 405),
            };
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    private function authorize(): void {
        if (in_array($this->entity, self::PUBLIC_ENTITIES, true)) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['unique_id'])) {
            throw new Exception('Authentication required.', 401);
        }

        if (in_array($this->requestMethod, ['PUT', 'DELETE'], true) || $this->action === 'publish') {
            $role = $_SESSION['role'] ?? '';
            if ($role !== 'admin' && $role !== 'super_admin') {
                throw new Exception('Admin privileges required.', 403);
            }
        }
        
        if ($this->action === 'getAll' && in_array($this->entity, ['complaints', 'payouts', 'users', 'results', 'payments'], true)) {
            $role = $_SESSION['role'] ?? '';
            if ($role !== 'admin' && $role !== 'super_admin') {
                throw new Exception('Admin privileges required.', 403);
            }
        }
        
        if (in_array($this->action, ['getOpenExams', 'getAllUsers'], true)) {
            $role = $_SESSION['role'] ?? '';
            if ($role !== 'admin' && $role !== 'super_admin') {
                throw new Exception('Admin privileges required.', 403);
            }
        }
    }

    private function handleGet(): void {
        switch ($this->action) {
            case 'getAll':         $this->getAll(); return;
            case 'get':            $this->get(); return;
            case 'getCurrentUser': $this->getCurrentUser(); return;
            case 'getBySlug':      $this->getBySlug(); return; 
            case 'getActive':      $this->getActiveAttempt(); return;
            case 'getHistory':     $this->getUserHistory(); return;
            case 'getMyComplaints':$this->getMyComplaints(); return;
            case 'getAnalytics':   $this->getExamAnalytics(); return;
            case 'getOpenExams':   $this->respond(true, (new Payment())->getOpenExams(), 'OK'); return;
            case 'getAllUsers':    $this->respond(true, (new Payment())->getAllUsers(), 'OK'); return;
            case 'getActiveExams': $this->respond(true, (new Exam())->getActiveExams(), 'Active exams loaded.'); return;            
            case 'getStudentStats': $this->respond(true, (new Dashboard())->getStudentStats((int)$_SESSION['user_id']), 'Stats loaded.'); return;
            default:
                $this->respond(false, null, 'Invalid GET action.', 400);
        }
    }

    private function handlePost(): void {
        if (isset(self::POST_ACTION_METHODS[$this->action])) {
            $route = self::POST_ACTION_METHODS[$this->action];
            $entityClass = self::ENTITY_CLASS[$route['entity']];
            $entity = new $entityClass();
            $method = $route['method'];
            
            if (!$method || !method_exists($entity, $method)) {
                throw new Exception("Method {$method} not found for {$route['entity']}.", 400);
            }

            if ($route['needs_user']) {
                $result = $entity->$method((int)$_SESSION['user_id'], $this->input);
            } else {
                $result = $entity->$method($this->input);
            }
            
            $this->respond(true, $result, 'Action executed successfully.');
            return;
        }

        switch ($this->action) {
            case 'create': $this->create(); return;
            case 'special': $this->special(); return;
            case 'publish': $this->publishExam(); return;
            case 'settle': $this->settlePayout(); return; 
            default:
                $this->respond(false, null, 'Invalid POST action.', 400);
        }
    }

    private function handlePut(): void {
        if (!$this->id) throw new Exception('ID is required.', 400);

        $entity = $this->makeEntity();
        $method = self::UPDATE_METHOD[$this->entity] ?? null;

        if (!$method || !method_exists($entity, $method)) {
            throw new Exception("Update not supported for '{$this->entity}'.", 400);
        }

        $result = $entity->$method((int)$this->id, $this->input);
        $this->respond(true, $result, 'Updated successfully.');
    }

    private function handleDelete(): void {
        if (!$this->id) throw new Exception('ID is required.', 400);
        
        $entity = $this->makeEntity();
        $method = self::DELETE_METHOD[$this->entity] ?? null;

        if (!$method || !method_exists($entity, $method)) {
            throw new Exception("Delete not supported for '{$this->entity}'.", 400);
        }

        $result = $entity->$method((int)$this->id);
        $this->respond(true, ['affected' => $result], 'Deleted successfully.');
    }

    private function publishExam(): void {
        $id = (int)($this->input['id'] ?? 0);
        if (!$id) throw new Exception('Exam ID is required to publish.', 400);

        (new Exam())->publish($id);
        $this->respond(true, null, 'Exam activated successfully. Students can now register.');
    }

    private function settlePayout(): void {
        $examId = (int)($this->input['exam_id'] ?? 0);
        if (!$examId) throw new Exception('exam_id is required.', 400);
        
        $result = (new Payout())->settleExam($examId);
        $this->respond(true, $result, 'Exam settled successfully.');
    }

    private function getAll(): void {
        $entity = $this->makeEntity();
        $method = self::LIST_METHOD[$this->entity] ?? null;

        if (!$method || !method_exists($entity, $method)) {
            throw new Exception("List not supported for '{$this->entity}'.", 400);
        }

        if (in_array($this->entity, ['questions', 'results'], true)) {
            $examId = (int)($_GET['exam_id'] ?? 0);
            if (!$examId) throw new Exception('exam_id is required.', 400);
            $data = $entity->$method($examId);
        } else {
            $data = $entity->$method();
        }

        $this->respond(true, $data, 'Retrieved successfully.');
    }

    private function get(): void {
        if (!$this->id) throw new Exception('ID is required.', 400);

        $entity = $this->makeEntity();
        $method = self::GET_METHOD[$this->entity] ?? null;

        if (!$method || !method_exists($entity, $method)) {
            throw new Exception("Get not supported for '{$this->entity}'.", 400);
        }

        $data = $entity->$method((int)$this->id);
        if (!$data) throw new Exception('Item not found.', 404);

        $this->respond(true, $data, 'Retrieved successfully.');
    }

    private function getBySlug(): void {
        $slug = $_GET['slug'] ?? '';
        if (!$slug) throw new Exception('Slug is required.', 400);

        $data = (new Exam())->getBySlug($slug);
        if (!$data) throw new Exception('Exam not found.', 404);

        $this->respond(true, $data, 'Retrieved successfully.');
    }

    private function getActiveAttempt(): void {
        $attemptId = (int)($_GET['attempt_id'] ?? 0);
        $userId = (int)$_SESSION['user_id'];
        $data = (new Attempt())->getActiveAttempt($userId, $attemptId);
        $this->respond(true, $data, 'Attempt loaded.');
    }

    private function getUserHistory(): void {
        $userId = (int)$_SESSION['user_id'];
        $data = (new Attempt())->getUserHistory($userId);
        $this->respond(true, $data, 'History loaded.');
    }

    private function getMyComplaints(): void {
        $userId = (int)$_SESSION['user_id'];
        $data = (new Complaint())->getMyComplaints($userId);
        $this->respond(true, $data, 'Complaints loaded.');
    }

    private function getExamAnalytics(): void {
        if (!$this->id) throw new Exception('Exam ID is required.', 400);
        
        $data = (new Exam())->getAnalytics((int)$this->id);
        $this->respond(true, $data, 'Analytics retrieved successfully.');
    }

    private function create(): void {
        if (empty($this->input)) throw new Exception('No data provided.', 400);

        $entity = $this->makeEntity();
        $method = self::CREATE_METHOD[$this->entity] ?? null;

        if (!$method || !method_exists($entity, $method)) {
            throw new Exception("Create not supported for '{$this->entity}'.", 400);
        }

        if ($this->entity === 'questions') {
            $examId = (int)($this->input['exam_id'] ?? 0);
            if (!$examId) throw new Exception('exam_id is required in the request body.', 400);
            $result = $entity->$method($examId, $this->input);
        } elseif ($this->entity === 'payments') {
            $examId = (int)($this->input['exam_id'] ?? 0);
            if (!$examId) throw new Exception('exam_id is required.', 400);
            $url = $entity->initializeExamEntry((int)$_SESSION['user_id'], $examId);
            $this->respond(true, ['url' => $url], 'Payment initialized.', 201);
            return;
        } elseif ($this->entity === 'attempts') {
            $examId = (int)($this->input['exam_id'] ?? 0);
            if (!$examId) throw new Exception('exam_id is required.', 400);
            $result = $entity->start((int)$_SESSION['user_id'], $examId);
        } elseif ($this->entity === 'complaints') {
            $result = $entity->create((int)$_SESSION['user_id'], $this->input);
        } else {
            $result = $entity->$method($this->input);
        }

        $this->respond(true, $result, 'Created successfully.', 201);
    }

    private function getCurrentUser(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) throw new Exception('Not logged in.', 401);

        $user = (new User())->getById((int)$_SESSION['user_id']);
        if (!$user) throw new Exception('User not found.', 404);

        $this->respond(true, $user, 'Current user retrieved.');
    }

    private function special(): void {
        if (empty($this->input)) throw new Exception('No data provided.', 400);

        if ($this->entity === 'auth') {
            $task = $this->input['task'] ?? '';
            $method = self::AUTH_TASKS[$task] ?? null;

            if (!$method) throw new Exception('Unknown auth task.', 400);

            $auth = new Auth();

            if ($task === 'register') {
                $result = (new User())->create($this->input);
                $result['email'] = $this->input['email'];
                $this->respond(true, $result, 'Account created.', 201);
            }

            $result = $auth->$method($this->input);
            $this->respond(true, $result, 'OK');
            return;
        }

        throw new Exception("Special action not supported for '{$this->entity}'.", 400);
    }

    private function makeEntity(): object {
        $class = self::ENTITY_CLASS[$this->entity] ?? null;
        if (!$class || !class_exists($class)) {
            throw new Exception("Unknown entity '{$this->entity}'.", 400);
        }
        return new $class();
    }

    private function respond(bool $success, $data = null, string $message = '', int $status = 200): void {
        http_response_code($status);
        echo json_encode([
            'success'   => $success,
            'data'      => $data,
            'message'   => $message,
            'requestId' => Logger::requestId(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function handleException(Throwable $e): void {
        $code    = $e->getCode() ?: 500;
        $httpCode = $this->httpCodeFor((int) $code);

        Logger::exception($e, ['entity' => $this->entity, 'action' => $this->action, 'http' => $httpCode]);

        $userMessage = $httpCode >= 500 ? 'An unexpected error occurred. Please try again.' : $e->getMessage();

        $payload = [
            'success' => false,
            'data'    => null,
            'message' => $userMessage,
            'requestId' => Logger::requestId(),
        ];

        if (Config::isDebug()) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ];
        }

        http_response_code($httpCode);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function httpCodeFor(int $code): int {
        return match (true) {
            in_array($code, [400, 401, 403, 404, 405, 409, 422, 429], true) => $code,
            default => 500,
        };
    }
}

// Bootstrap
(new ApiHandler())->handleRequest();