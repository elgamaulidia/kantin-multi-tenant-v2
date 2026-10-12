<?php

namespace Tests\Unit;

use App\Support\Tokens\OpaqueToken;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OpaqueTokenTest extends TestCase
{
    public function test_token_yang_dibuat_berbeda(): void
    {
        $tokenPertama = OpaqueToken::issue();
        $tokenKedua = OpaqueToken::issue();

        $this->assertNotSame(
            $tokenPertama['plain'],
            $tokenKedua['plain']
        );
    }

    public function test_hash_token_memiliki_panjang_32_byte(): void
    {
        $token = OpaqueToken::issue();

        $this->assertSame(32, strlen($token['hash']));
    }

    public function test_token_menggunakan_karakter_url_aman(): void
    {
        $token = OpaqueToken::issue();

        $this->assertMatchesRegularExpression(
            '/^[A-Za-z0-9_-]+$/',
            $token['plain']
        );
    }

    public function test_hash_token_sama_untuk_token_yang_sama(): void
    {
        $plain = 'contoh-token-untuk-pengujian';

        $this->assertSame(
            OpaqueToken::hash($plain),
            OpaqueToken::hash($plain)
        );
    }

    public function test_token_menolak_panjang_di_bawah_128_bit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OpaqueToken::issue(15);
    }
}