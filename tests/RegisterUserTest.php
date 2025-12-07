<?php
use PHPUnit\Framework\TestCase;

class RegisterUserTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = $this->createMock(mysqli::class);
        include_once '../RegisterPage/Register.php';
    }

    public function testPasswordsDoNotMatch()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Пароли не совпадают.');

        Register($this->conn, 'testuser', 'password1', 'password2');
    }


    public function testLoginAlreadyExists()
    {
        $mockStmt = $this->createMock(mysqli_stmt::class);

        $this->conn->method('prepare')
            ->with("SELECT login FROM user WHERE login = ?")
            ->willReturn($mockStmt);
        $mockStmt->method('bind_param')
            ->with("s", $this->anything());
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('store_result');
        $mockStmt->method('num_rows')->willReturn(1);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Логин уже существует.');

        Register($this->conn, 'existinguser', 'password', 'password');
    }

    public function testEmptyFields()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Все поля должны быть заполнены.');

        Register($this->conn, '', 'password', 'password');
        Register($this->conn, 'testuser', '', 'password');
        Register($this->conn, 'testuser', 'password', '');
    }


}