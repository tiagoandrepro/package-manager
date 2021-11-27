<?php

namespace Tiagoandrepro\Lmsquid\Interfaces;

interface ModuleRegister
{
    public function configure(): array;
    public function depends(): null|array;
}
