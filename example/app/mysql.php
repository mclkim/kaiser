<?php
use \Kaiser\Controller;

/**
 * http://localhost/test/public/?mysql
 */

/**
 * $container ['QB'] = function ($c) {
 * // Create a connection, once only.
 * $config = array(
 * 'driver'    => 'mysql', // Db driver
 * 'host'      => 'localhost',
 * 'database'  => 'your-database',
 * 'username'  => 'root',
 * 'password'  => 'your-password',
 * 'charset'   => 'utf8', // Optional
 * 'collation' => 'utf8_unicode_ci', // Optional
 * 'prefix'    => 'cb_', // Table prefix, optional
 * 'options'   => array( // PDO constructor options, optional
 * PDO::ATTR_TIMEOUT => 5,
 * PDO::ATTR_EMULATE_PREPARES => false,
 * ),
 * );
 *
 * return new \Pixie\Connection('mysql', $config, 'QB');
 * };
 */
class mysql extends Controller
{
    protected function requireLogin()
    {
        return false;
    }

    function connection()
    {
        try {
            $dbname = 'book_ex';
            $user = 'zerock';
            $pass = 'zerock';
            $dbh = new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $pass);
            foreach ($dbh->query('SELECT version()') as $row) {
                print_r($row);
            }
            $dbh = null;
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    function query()
    {
        $pdo = $this->container->get('QB');
        $dbm = new  \Kaiser\Manager\DBManager ($pdo);
        var_dump($dbm->executePreparedQueryOne('select * from tbl_attach'));
    }

    function execute()
    {
        $qb = $this->container->get('QB');
        var_dump($qb);

        $query = QB::query('select version()');
        var_dump($query->get());
    }
}