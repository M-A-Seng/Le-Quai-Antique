<?php

namespace App\Tests\Services;

use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidFieldException;
use App\Models\UserModel;
use App\Services\UserService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\once;

class UserServicesTest extends TestCase
{
    ################################
    # DATA PROVIDERS
    ################################

    public static function validEmailProvider(): array {
        return [
            'gmail' => ['test@gmail.com'],
            'yahoo' => ['test@yahoo.com'],
            'outlook' => ['test@outlook.com'],
            'protonmail' => ['test@protonmail.com'],
            'icloud' => ['test@icloud.com'],
            'live' => ['test@live.com'],
            'aol' => ['test@aol.com'],
            'gmx' => ['test@gmx.net'],
            'mailfr' => ['test@mail.fr'],
            'laposte' => ['test@laposte.net'],
            'tutanota' => ['test@tutanota.co'],
            'orange' => ['test@orange.fr'], 
            'with dot' => ['test.test@gmail.com'],
            'with dash' => ['test-test@support.microsoft.com'],
            'with plus' => ['test+test@gmail.com'],
            'numeric local part' => ['12345@gmail.com'],
            'subdomain' => ['test@email.ticketmaster.com'],
            'uppercase' => ['TEST@GMAIL.COM'],
        ];
    }
    public static function invalidEmailProvider(): array {
        return [
            'empty string' => [''],
            'missing at' => ['plainaddress'],
            'missing local' => ['@gmail.com'],
            'double at' => ['test@@gmail.com'],
            'space in local' => ['test test@gmail.com'],
            'special char start' => ['<test@gmail.com'],
            'forbidden special char' => ['test:test@gmail.com'],
            'trailing dot' => ['test.@gmail.com'],
            'leading dot' => ['.test@gmail.com'],
            'nonexistent domain' => ['test@thisdomaindoesnotexist12345.com'],
            'domain with no MX' => ['test@example.co'],
        ];
    }
    public static function validPasswordProvider(): array {
        return [
            'simple strong' => ['Aa1!aaaa', 'Aa1!aaaa'],
            'longer complex' => ['P@ssw0rd123!', 'P@ssw0rd123!'],
            'mixed special chars' => ['Xy9$z!9a', 'Xy9$z!9a'],
            'all categories present' => ['Abcdef1#', 'Abcdef1#'],
            'long with symbols' => ['MyC0mpl3x&P@ss', 'MyC0mpl3x&P@ss'],
        ];
    }
    public static function invalidPasswordProvider(): array {
        return [
            'too short' => ['Aa1!a', 'Aa1!a'],
            'no uppercase' => ['aa1!aaaa', 'aa1!aaaa'],
            'no lowercase' => ['AA1!AAAA', 'AA1!AAAA'],
            'no digit' => ['Aa!aaaaa', 'Aa!aaaaa'],
            'no special' => ['Aa1aaaaa', 'Aa1aaaaa'],
            'empty string' => ['', ''],
            'all lowercase' => ['abcdefgh!1', 'abcdefgh!1'],
            'all uppercase' => ['ABCDEFGH!1', 'ABCDEFGH!1'],
            'only digits' => ['12345678!A', '12345678!A'],
            'only letters' => ['Abcdefgh!', 'Abcdefgh!'],
            'good password but fail confirm' => ['MyC0mpl3x&P@ss', 'MyC0mpl3xP@ss'],
        ];
    }
    public static function validPhoneProvider(): array {
        return [
            'international no plus' => ['33612345678', '33612345678'],
            'international with plus' => ['+33612345678', '+33612345678'],
            'international with spaces' => ['+33 6 12 34 56 78', '+33 6 12 34 56 78'],
            'international with dashes' => ['+33-6-12-34-56-78', '+33-6-12-34-56-78'],
            'national starting zero' => ['0612345678', '0612345678'],
            'national with spaces' => ['06 12 34 56 78', '06 12 34 56 78'],
            'national with dashes' => ['06-12-34-56-78', '06-12-34-56-78'],
            'longer intl' => ['+4915123456789', '+4915123456789'],
            'minimum length intl' => ['+1234567', '+1234567'],
            'maximum length intl' => ['+123456789012345', '+123456789012345'],
            'starts with 00' => ['0033612345678', '0033612345678'],
            'space only' => ['          ', NULL], # Ok car le tel est facultatif
            'empty string' => ['', NULL], # Ok car le tel est facultatif

        ];
    }
    public static function invalidPhoneProvider(): array {
        return [
            'too short intl' => ['+123456'],
            'too long intl' => ['+1234567890123456789'],
            'invalid character letters' => ['+33 6 12A 34 56'],
            'invalid character symbols' => ['+33 6 12@34#56'],
            'starts with 0 and plus' => ['0+612345678'],
            'multiple plus' => ['++33612345678'],
            'only zeros' => ['0000000'],
        ];
    }
    public static function validUserSignupDataProvider(): array {
        return [
            'mandatory keys only' => [[
                "last_name" => "test",
                "email" => "test@gmail.com",
                "password" => "MyC0mpl3x&P@ss",
                "password-confirm" => "MyC0mpl3x&P@ss"
            ]],
            'with phone number' => [[
                "last_name" => "test",
                "email" => "test@gmail.com",
                "password" => "MyC0mpl3x&P@ss",
                "password-confirm" => "MyC0mpl3x&P@ss",
                "tel" => "+33612345678",
            ]],
            'with allergies' => [[
                "last_name" => "test",
                "email" => "test@gmail.com",
                "password" => "MyC0mpl3x&P@ss",
                "password-confirm" => "MyC0mpl3x&P@ss",
                "allergy" => [
                    0 => 'gluten',
                    1 => 'arachides',
                    2 => 'lait'
                ]
            ]],
            'more optionnal keys' => [[
                "first_name" => "test",
                "last_name" => "test",
                "email" => "test@gmail.com",
                "password" => "MyC0mpl3x&P@ss",
                "password-confirm" => "MyC0mpl3x&P@ss",
                "tel" => "+33612345678",
                "allergy" => [
                    0 => 'gluten',
                    1 => 'arachides',
                    2 => 'lait'
                ],
                "default_guest_count" => "4", 
            ]],
        ];
    }
    public static function invalidInputKeysProvider(): array {
        return [
            'extra key' => [['first_name' => 'test', 'email' => 'test@gmail.com', 'password' => 'password', 'csrf_token' => 'abc123']],
            'wrong key' => [['first_name' => 'test', 'email' => 'test@gmail.com', 'password' => 'password']],
            'one key and a wrong one' => [['first_name' => 'test']],
            'csrf missing' => [['email' => 'test@gmail.com', 'password' => 'password']],
            'input missing' => [['email' => 'test@gmail.com', 'csrf_token' => 'abc123']],
        ];
    }
    public static function invalidCredentialsProvider(): array {
        return [
            'password unmatched' => [['email' => 'test@gmail.com', 'password' => 'password', 'csrf_token' => 'abc123'], 
                        ['email' => 'test@gmail.com', 'password' => 'unmatched']],
            'user not found' => [['email' => 'test@gmail.com', 'password' => 'password', 'csrf_token' => 'abc123'], 
                        []],
        ];
    }
    ################################
    # TESTS
    ################################

    private UserService $obj;
    private $userModelMock;
    public function setUp(): void
    {
        $this->userModelMock = $this->createMock(UserModel::class);
        $this->obj = new UserService($this->userModelMock);
        $_SESSION['id'] = 1;
    }

    #[DataProvider('validEmailProvider')]
    public function testEmailCheckSuccess(string $email): void
    {
        $this->userModelMock->expects($this->once())
                ->method('getUserByEmail')
                ->with(strtolower($email));
        
        $result = $this->obj->emailCheck($email, true);
        $this->assertEquals(true, $result);
    }

    #[DataProvider('invalidEmailProvider'), AllowMockObjectsWithoutExpectations]
    public function testEmailCheckThrowsException(string $email): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->emailCheck($email, true);
    }

    #[DataProvider('validPasswordProvider'), AllowMockObjectsWithoutExpectations]
    public function testPasswordCheckSuccess(string $password, string $passwordConfirm): void
    {
        $this->obj->passwordCheck($password, $passwordConfirm);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidPasswordProvider'), AllowMockObjectsWithoutExpectations]
    public function testPasswordCheckThrowsException(string $password, string $passwordConfirm): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->passwordCheck($password, $passwordConfirm);
    }

    #[DataProvider('validPhoneProvider'), AllowMockObjectsWithoutExpectations] 
    public function testPhoneNumberCheckAndSanitizeSuccess(string $phoneNumber, string|null $expected): void
    {
        $result = $this->obj->phoneNumberCheckAndSanitize($phoneNumber);
        $this->assertEquals($expected, $result);
    }

    #[DataProvider('invalidPhoneProvider'), AllowMockObjectsWithoutExpectations] 
    public function testPhoneNumberAndSanitizeThrowsException(string $phoneNumber): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->phoneNumberCheckAndSanitize($phoneNumber);
    }

    #[DataProvider('validUserSignupDataProvider')]
    public function testSignUserUpSuccess(array $data): void
    {
        $this->userModelMock->expects($this->once())
        ->method('createUser');
        
        $this->obj->signUserUp($data);
        $this->addToAssertionCount(1);
    }

    public function testAuthenticateUserSuccess(): void
    {
        $data = ['email' => 'test@gmail.com', 'password' => 'password', 'csrf_token' => 'abc123'];
        $dbResult = ['id' => 1, 'role' => 'role', 'password' => '$2y$10$YwY3YHqHdTOkvxZdVXoSK.mY4TWGEesAB9iAGufpA0jnSCrVJnGq.'];
        $this->userModelMock->expects($this->once())
                ->method('getUserByEmail')
                ->with($data['email'])
                ->willReturn($dbResult);
        
        $result = $this->obj->authenticateUser($data);
        $this->assertEquals(['id' => 1, 'role' => 'role'], $result);
    }

    #[DataProvider('invalidInputKeysProvider'), AllowMockObjectsWithoutExpectations]
    public function testAuthenticateUserThrowsInputException(array $data): void
    {
        $this->expectException(InvalidFieldException::class);
        $this->obj->authenticateUser($data);
    }

    #[DataProvider('invalidCredentialsProvider')]
    public function testAuthenticateUserThrowsCredentialException(array $data, array $dbResult): void
    {
        $this->userModelMock->expects(once())
                ->method('getUserByEmail')
                ->with($data['email'])
                ->willReturn($dbResult);

        $this->expectException(InvalidCredentialsException::class);
        $this->obj->authenticateUser($data);
    }

    public function testUpdateUserProfileSuccess()
    {
        $data = ["first_name" => "test", "email" => "test@yahoo.fr", "allergy" => "soja"];
        $this->userModelMock->expects(once())
                ->method('updateUser')
                ->with(1, $data);

        $this->obj->updateUserProfile($data);
        $this->addToAssertionCount(1);
    }

    public function testDeleteUserAccountSuccess()
    {
        $this->userModelMock->expects(once())
                ->method('deleteUser')
                ->with(1);

        $this->obj->deleteUserAccount(1);
        $this->addToAssertionCount(1);
    }
}