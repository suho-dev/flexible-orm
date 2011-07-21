<?php
/**
 * @file
 * @author jarrod.swift
 */
namespace ORM\Tests\Mock;
use ORM\ORM_Model;
/**
 * Description of AlternateCar
 *
 * A simple Model using a separate database
 */
class Window extends ORM_Model {
    const DATABASE  = 'clients';
    
    private static $_arWindows;

    public static function LoadAccurateWindows() {
        self::$_arWindows = file('/server/projects/WindowManufacturers.csv');

        // Tidy up input
        array_walk(self::$_arWindows, function(&$w){
            $w = trim( substr(trim($w), 1, -2) );
        });

        return self::$_arWindows;
    }

    public static function FindMostLikely( $accurateWindowDescription ) {
        if ( stripos($accurateWindowDescription, 'Low-E') !== false ) {
            preg_match( '/^([^:]+):([^-:]*-?)([^-]+)-([^-:]+):?([^:]*Low-E)$/', $accurateWindowDescription, $matches );
        } elseif ( !preg_match('/^([^:]+):([^-:]*-?)([^-]+)-([^-:]+):?([^:-]*)$/', $accurateWindowDescription, $matches ) ) {
            echo( "[ERROR] Unable to split up '$accurateWindowDescription'!\n");
            return false;
        }

        $manufacturer   = trim($matches[1]);
        $description    = trim($matches[3]) ? trim($matches[3]) : trim($matches[2]);
        $frameType      = trim($matches[4]); // double glazed/single glazed
        $glazing        = trim($matches[5]);

        $glazingSearch = self::TranslateGlazing( $glazing );

        // Attempt straight match
        $windows = Window::FindAll(array(
            'where'     => 'name LIKE ? AND manufacturer LIKE ? AND TRIM(glazing) LIKE ?',
            'values'    => array("%$description%","$manufacturer%", $glazingSearch)
        ));

        if ( count($windows) ) {
//            echo "Found ", count($windows), " results:";
//            echo "\t {$windows->current()->describe()}\n\n";

            return $windows->current();
        } else {
            echo "Unable to locate an exact match for:\n\t$accurateWindowDescription\n";
            echo "\t", "name LIKE '%$description%' AND manufacturer LIKE '$manufacturer%' AND TRIM(glazing) LIKE '$glazingSearch'\n\n";
            return false;
        }

    }

    public static function TranslateGlazing( $glazingDescription ) {
        $layers = explode('/', $glazingDescription);
        $glazing = '';
        foreach ($layers as $i => $layer ) {
            if ( $i > 0 ) {
                $glazing .= '/';
            }
            preg_match('/^([0-9.]+)mm (.*)$/', $layer, $matches);
            $glazing .= $matches[1];

            switch( $matches[2] ){
                case 'Clear':
                    if ( count($layers) == 1 ) {
                        $glazing .= 'Clr';
                    }
                    break;
                case 'Laminated Grey':
                    $glazing .= 'Gy';
                    break;
                case 'EverGreen':
                    $glazing .= 'EG';
                    break;
                case 'Sunergy Clear':
                    $glazing .= 'Sn';
                    break;
                case 'Solarcool Azure':
                    $glazing .= 'SCAz';
                    break;
                case 'Solarcool Bronze':
                    $glazing .= 'SCBz';
                    break;
                case 'Solarcool Grey':
                    $glazing .= 'SCGy';
                    break;
                case 'Green':
                    $glazing .= 'Gn';
                    break;
                case 'ComfortPlus Clear':
                    $glazing .= 'CPClr';
                    break;
                case 'ComfortPlus Neutral':
                    $glazing .= 'CP';
                    break;
                case 'Energy Advantage Low-E':
                    $glazing .= 'EA';
                    break;

                default:
                    
            }
        }

        return $glazing;
    }

    public function manufacturerWindowName() {
        preg_match('/([^-]+)- /', $this->name, $matches );
        return $matches[1];
    }

    public function doubleGlazed() {
        return stripos($this->name, 'Double Glazed') !== false;
    }

    public function describe() {
        return $this->name . " " . $this->glazing;
    }
}
?>
