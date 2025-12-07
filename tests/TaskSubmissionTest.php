<?php
use PHPUnit\Framework\TestCase;

class TaskSubmissionTest extends TestCase
{
    protected $mockConn;
    protected $mockStmt;

    protected function setUp(): void
    {
        require_once '../Client/MainPage/SubmitRequest.php';

        $this->mockConn = $this->createMock(mysqli::class);
        $this->mockStmt = $this->createMock(mysqli_stmt::class);

        $this->mockConn->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->method('execute')
            ->willReturn(true);
        $this->mockStmt->method('bind_param')
            ->willReturn(true);
        $this->mockStmt->method('fetch')
            ->willReturn(true);
        $this->mockStmt->method('bind_result')
            ->will($this->returnCallback(function (&$client_id) {
                $client_id = 1;
            }));

        $_SESSION['login'] = 'test';

        $_FILES['task-photo'] = [
            'name' => 'test.jpg',
            'tmp_name' => '/path/to/tmp/file',
            'size' => 500000,
            'error' => 0
        ];
    }

    public function testMissingFormFields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Пожалуйста, заполните все поля формы.');

        submitRequest($this->mockConn, '', 'Test Description', 1, $_FILES['task-photo']);
        submitRequest($this->mockConn, 'Test', '', 1, $_FILES['task-photo']);

    }

    public function testFileUploadWithInvalidFormat()
    {
        $_FILES['task-photo']['name'] = 'invalid_file.txt';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Неподдерживаемый формат файла. Разрешены только JPG, PNG, GIF.');
        submitRequest($this->mockConn, 'task_name', 'task_description', 1, $_FILES['task-photo']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['login']);
        unset($_FILES);
    }
}
