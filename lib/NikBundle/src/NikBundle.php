<?php

namespace AMREU\NikBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NikBundle extends Bundle
{
   public function getAlias(): string
   {
      return "nik";
   }
}
