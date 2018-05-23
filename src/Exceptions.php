<?php

namespace Mcl\Kaiser;

class ExceptionBase extends \Exception
{
}

class ApplicationException extends ExceptionBase
{
}

class AjaxException extends ExceptionBase
{
}

class EchoException extends ExceptionBase
{
}
