<?php


namespace App\Extension\Twig\Env;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Env extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getEnv',[$this,'getEnv'])
        ];
    }

    public function getEnv(string $varName): string
    {
        return (string)getenv($varName);
    }
}
