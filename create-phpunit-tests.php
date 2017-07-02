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

if (!mkdir("$dir/test")) {
	exit("Can't create dir 'test'");
}

$bootstrap = trim("
<?php

class Psr4AutoloaderClass
{
    protected \$prefixes = array();

    public function register()
    {
        spl_autoload_register(array(\$this, 'loadClass'));
    }

    public function addNamespace(\$prefix, \$base_dir, \$prepend = false)
    {
        \$prefix = trim(\$prefix, '\\\') . '\\\';
        \$base_dir = rtrim(\$base_dir, DIRECTORY_SEPARATOR) . '/';
        if (isset(\$this->prefixes[\$prefix]) === false) {
            \$this->prefixes[\$prefix] = array();
        }
		
        if (\$prepend) {
            array_unshift(\$this->prefixes[\$prefix], \$base_dir);
        } else {
            array_push(\$this->prefixes[\$prefix], \$base_dir);
        }
    }

    public function loadClass(\$class)
    {
        \$prefix = \$class;
        while (false !== \$pos = strrpos(\$prefix, '\\\')) {
            \$prefix = substr(\$class, 0, \$pos + 1);
            \$relative_class = substr(\$class, \$pos + 1);
            \$mapped_file = \$this->loadMappedFile(\$prefix, \$relative_class);
            if (\$mapped_file) {
                return \$mapped_file;
            }
            \$prefix = rtrim(\$prefix, '\\\');
        }
        return false;
    }

    protected function loadMappedFile(\$prefix, \$relative_class)
    {
        if (isset(\$this->prefixes[\$prefix]) === false) {
            return false;
        }
        foreach (\$this->prefixes[\$prefix] as \$base_dir) {

            \$file = \$base_dir
                  . str_replace('\\\', '/', \$relative_class)
                  . '.php';
            if (\$this->requireFile(\$file)) {
                return \$file;
            }
        }
        return false;
    }

    protected function requireFile(\$file)
    {
        if (file_exists(\$file)) {
            require \$file;
            return true;
        }
        return false;
    }
}

\$loader = new Psr4AutoloaderClass();
\$loader->addNamespace('test', __DIR__.'/../test');
\$loader->addNamespace('your_namescape', __DIR__.'/../src');

");
file_put_contents("$dir/test/bootstrap.php", $bootstrap);

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
file_put_contents("$dir/test/general.php", $general);

exit("Done");
