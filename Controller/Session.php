<?php
/**
 * @file
 * @author Pierre Dumuid <pierre.dumuid@sustainabilityhouse.com.au>
 */
namespace ORM\Controller;

/**
 * A wrapper for the session variables
 *
 * Provides way to avoid locked session when multiple PHP pages are requested.
 *
 * When a web server receives multiple PHP page request from a single user simultaneous,
 * Apache will block each process until the others are finished.
 *
 * In some cases, the blocking is undesirable, particularly if the session data is used in a
 * read-only manner (i.e. to display results without any update).
 *
 * <b>Usage</b>
 *
 * Where an update is performed:
 *
 * @code
 * $session = \ORM\Controller\Session::getSession(true);
 * $session->set("i", 1, false);
 * $session->set("j", 1, false);
 * $session->set("k", 1);
 * @endcode
 *
 * Where data only needs to be retrieved:
 *
 * @code
 * $session = \ORM\Controller\Session::getSession();
 * $i = $session->get("i");
 * echo "The value of i is $i";
 * @endcode
 *
 * Loading session, and subsequently locking it.
 *
 * @code
 * // Construction results in loading with a default of non-blocking behaviour
 * $session = \ORM\Controller\Session::getSession();
 *
 * // How to set variables.
 *
 * $j = $session->get("j");
 * $session->loadSessionVariable(true);
 * $i = $session->get("i");
 * $session->set("i",$i + 1,false);   // Good
 * $session->set("j",$j + 1,false);   // BAD - because "j" may have been modified by another script!
 * $session->saveSessionVariable();
 * @endcode
 *
 * @todo Add a variable to mark the session as blocking or not.
 * @todo Consider implementing variables in an extension of \ArrayObject.
 * @todo Consider marking items within the \ArrayObject as read-only unless opened in a non-blocking fashion.
 *
 */
class Session {
    /**
     * This class is a singleton, and the following variable contains the respective data.
     *
     * @var Session $_session
     */
    protected static $_session;

    /**
     * Variable to hold the variables retrieved from the session between session openings.
     *
     * @var array $_sessionVariableCache
     */
    private $_sessionVariableCache;

    /**
     * A boolean to determine if the session is locked (i.e. still open).
     *
     * @var bool $_locked
     */
    private $_locked = false;

    /**
     * A boolean to determine if the data has not been saved.
     *
     * @var bool $_unsavedData
     */
    private $_unsavedData = false;

    /**
     * Constant the defines the session name
     */
    const SESSION_NAME = 'ORM_DEFAULT';

    /**
     * Constant the defines the field of the $_SESSION variable to store the ORM values in.
     */
    const FIELD_NAME = 'ORM';

    /**
     * Session is a singleton class
     *
     */
    private function __construct() {}

    /**
     * Get the static Session instance and instantiate if necessary
     *
     * @return Session
     */
    public static function getSession($lock = false) {
        if( is_null(static::$_session) ) {
            $calledClass = get_called_class();
            static::$_session = new $calledClass();
            static::$_session->loadSessionVariable($lock);
        }

        return static::$_session;
    }

    /**
     * Retrieve variables from global session variable and store it in local cache
     */
    public function loadSessionVariable($lock = false) {
        session_name(static::SESSION_NAME);
        if (!$this->_locked) {
            session_start();
        }
        $this->_sessionVariableCache = array_key_exists(static::FIELD_NAME, $_SESSION) ? $_SESSION[static::FIELD_NAME] : array();
        if (!$lock) {
            session_write_close();
            $this->_locked = false;
        } else {
            $this->_locked = true;
        }
    }

    /**
     * Save variables to session
     */
    public function saveSessionVariable() {
        if (!$this->_locked) {
            throw new \LogicException("Session is not in a locked condition, unable to update session variable.");
        }
        $_SESSION[static::FIELD_NAME] = $this->_sessionVariableCache;
        $this->_unsavedData = false;
        session_write_close();
        $this->_locked = false;
    }

    /**
     * Retrieve a variable from the local cached variable array.
     *
     * @return mixed
     */
    public function get($var) {
        return array_key_exists($var, $this->_sessionVariableCache) ? $this->_sessionVariableCache[$var] : null;
    }

    /**
     * Set a variable in the local cached variable array.
     *
     * @return mixed
     */
    public function set($var, $value, $save = true) {
        if (!$this->_locked) {
            throw new \LogicException("Attempt to set session variable when Session is not in a locked condition.");
        }
        $this->_sessionVariableCache[$var] = $value;
        if ($save) {
            $this->saveSessionVariable();
        } else {
            $this->_unsavedData = true;
        }
    }

    public function clear($var) {
        unset($this->_sessionVariableCache[$var]);
    }

    public function __destruct() {
        if ($this->_unsavedData) {
            $this->saveSessionVariable();
        }
    }
}
