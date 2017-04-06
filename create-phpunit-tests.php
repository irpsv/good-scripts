<?php

$dir = $argv[1] ?? null;
if (false == ($dir && file_exists($dir) && is_dir($dir))) {
	exit("Not found dir '{$dir}'");
}

$phpunit = trim("
<phpunit bootstrap='tests/bootstrap.php'>
    <testsuites>
        <testsuite name='all'>
            <directory suffix='.php'>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
");
file_put_contents("$dir/phpunit.xml", $phpunit);

if (!mkdir("$dir/tests")) {
	exit("Can't create dir 'tests'");
}

$bootstrap = "<?php\n\n";
file_put_contents("$dir/tests/bootstrap.php", $bootstrap);

$general = trim("
<?php

use PHPUnit\Framework\TestCase;

class GeneralTest extends TestCase
{
    public function testName()
    {
		
    }
}
");
file_put_contents("$dir/tests/general.php", $general);

exit("Done");
