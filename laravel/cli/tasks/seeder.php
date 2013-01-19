<?php namespace Laravel\CLI\Tasks;

use Laravel\Database;

class Seeder extends Task {

    /**
     * The database seed file list.
     *
     * @var array
     */
    protected $seeds;

    /**
     * Seed the database from the given path.
     *
     * @param array   $arguments
     * @return int
     */
    public function seed( $arguments = array() ) {
        $path = array_get( $arguments, 0, path( 'app' ) . DS . 'seeds' );

        $total = 0;

        foreach ( $this->getFiles( $path ) as $file ) {
            $records = $this->getRequire( $file );

            // We'll grab the table name here, which could either come from the array or
            // from the filename itself. Then, we will simply insert the records into
            // the databases.
            $table = $this->getTable( $records, $file );

            Database::table( $table )->delete();

            Database::table( $table )->insert( $records );

            $total += $count = count( $records );
            echo sprintf( "Seeded: `%s` (%d rows)\n", $table, $count );
        }

        return $total;
    }


    /**
     * Get all of the files at a given path.
     *
     * @param string  $path
     * @return array
     */
    protected function getFiles( $path ) {
        if ( isset( $this->seeds ) ) return $this->seeds;

        // If the seeds haven't been read before, we will glob the directory and sort
        // them alphabetically just in case the developer is using numbers to make
        // the seed run in a certain order based on their database design needs.
        $files = glob( $path . DS . '*.php' );

        sort( $files );

        return $this->seeds = $files;
    }

    /**
     * Get the table from the given records and file.
     *
     * @param array   $records
     * @param string  $file
     * @return string
     */
    protected function getTable( & $records, $file ) {
        $table = array_get( $records, 'table', basename( $file, '.php' ) );

        unset( $records['table'] );

        return $table;
    }

    /**
     * Get the returned value of a file.
     *
     * @param string  $path
     * @return mixed
     */
    public function getRequire( $path ) {
        if ( file_exists( $path ) ) return require $path;

        throw new FileNotFoundException( "File does not exist at path {$path}" );
    }

}
