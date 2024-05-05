<?php

use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;


static $key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9@';

function SignIn($data)
{

    $token = (new JwtFacade())->issue(
        new Sha256(),
        InMemory::plainText(base64_encode(password_hash($this->$key, PASSWORD_DEFAULT, ['cost' => 8]))),
        static fn (
            Builder $builder,
            DateTimeImmutable $issuedAt,
        ): Builder => $builder
            ->issuedBy('localhost')
            ->permittedFor(static::Aud())
            ->expiresAt($issuedAt->modify('+5000 minutes'))
            ->withClaim('data', $data)
    );

    return $token->toString();
}

function Check(String $generated)
{

    try {

        $clock = new FrozenClock(new DateTimeImmutable());
        $parser = new Parser(new JoseEncoder());
        $config = Configuration::forUnsecuredSigner();

        $constraints = [
            new PermittedFor($this->Aud()),
            new IssuedBy('localhost'),
            new LooseValidAt($clock),
        ];

        return $config->validator()->validate($parser->parse($generated), ...$constraints);
    } catch (\Throwable $th) {

        return false;
    }
}

function GetData(String $generated)
{

    $config = Configuration::forUnsecuredSigner();
    $read = $config->parser()->parse($generated);
    assert($read instanceof Token\Plain);

    return $read->claims()->get('data');
}

function Aud()
{

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $aud = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $aud = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $aud = $_SERVER['REMOTE_ADDR'];
    }
    $aud .= @$_SERVER['HTTP_USER_AGENT'];
    $aud .= gethostname();

    return sha1($aud);
}