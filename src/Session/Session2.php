<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session;

use Windwalker\Session\Bag\FlashBag;
use Windwalker\Session\Bag\FlashBagInterface;
use Windwalker\Session\Bag\SessionBag;
use Windwalker\Session\Bag\SessionBagInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\SessionBridgeInterface;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\NativeHandler;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides
 * more advanced features such as expire timeouts.
 *
 * @since  {DEPLOY_VERSION}
 */
class Session2 implements \IteratorAggregate
{
	const STATE_RESTART = 'restart';

	const STATE_ACTIVE = 'active';

	const STATE_INACTIVE = 'inactive';

	const STATE_EXPIRED = 'expired';

	const STATE_DESTROYED = 'destroyed';

	const STATE_ERROR = 'error';

	/**
	 * Internal state.
	 * One of 'inactive'|'active'|'expired'|'destroyed'|'error'
	 *
	 * @var    string
	 * @see    getState()
	 * @since  {DEPLOY_VERSION}
	 */
	protected $state = 0;

	/**
	 * Maximum age of unused session in minutes
	 *
	 * @var    string
	 * @since  {DEPLOY_VERSION}
	 */
	protected $expireTime = 15;

	/**
	 * The session store object.
	 *
	 * @var    \Windwalker\Session\Handler\HandlerInterface
	 * @since  {DEPLOY_VERSION}
	 */
	protected $handler = null;

	/**
	 * Security policy.
	 * List of checks that will be done.
	 *
	 * Default values:
	 * - fix_browser
	 * - fix_adress
	 *
	 * @var    array
	 * @since  {DEPLOY_VERSION}
	 */
	protected $security = array('fix_browser');

	/**
	 * Force cookies to be SSL only
	 * Default  false
	 *
	 * @var    boolean
	 * @since  {DEPLOY_VERSION}
	 */
	protected $forceSSL = false;

	/**
	 * The domain to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  {DEPLOY_VERSION}
	 */
	protected $cookieDomain;

	/**
	 * The path to use when setting cookies.
	 *
	 * @var    mixed
	 * @since  {DEPLOY_VERSION}
	 */
	protected $cookiePath;

	/**
	 * Property cookie.
	 *
	 * @var  array
	 */
	protected $cookie = null;

	/**
	 * Property bags.
	 *
	 * @var  SessionBagInterface[]
	 */
	protected $bags = array();

	/**
	 * Property bridge.
	 *
	 * @var  SessionBridgeInterface
	 */
	protected $bridge = null;

	/**
	 * Constructor
	 *
	 * @param   HandlerInterface     $handler The type of storage for the session.
	 * @param   SessionBagInterface  $bag
	 * @param   FlashBagInterface    $flashBag
	 * @param   array                $options Optional parameters
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function __construct(HandlerInterface $handler = null, SessionBagInterface $bag = null,
		FlashBagInterface $flashBag = null, array $options = array())
	{
		$this->bridge = new NativeBridge;

		// Create handler
		$this->handler = $handler ? : new NativeHandler;

		$bags = array(
			'default' => $bag ? : new SessionBag,
			'flash'   => $flashBag ? : new FlashBag
		);

		$this->setBags($bags);

		// Set options
		$this->setOptions($options);

		$this->setCookieParams();

		$this->state = static::STATE_INACTIVE;
	}

	/**
	 * getCookie
	 *
	 * @return  array
	 */
	public function getCookie()
	{
		return $this->cookie;
	}

	/**
	 * setCookie
	 *
	 * @param   array $cookie
	 *
	 * @return  Session  Return self to support chaining.
	 */
	public function setCookie($cookie)
	{
		$this->cookie = $cookie;

		return $this;
	}

	/**
	 * registerHandler
	 *
	 * @return  void
	 */
	protected function registerHandler()
	{
		$this->handler->register();
	}

	/**
	 * getBags
	 *
	 * @return  array
	 */
	public function getBags()
	{
		return $this->bags;
	}

	/**
	 * setBags
	 *
	 * @param   SessionBagInterface[] $bags
	 *
	 * @return  Session  Return self to support chaining.
	 */
	public function setBags(array $bags)
	{
		foreach ($bags as $name => $bag)
		{
			$this->setBag($name, $bag);
		}

		return $this;
	}

	/**
	 * getBag
	 *
	 * @param string $name
	 *
	 * @throws  \UnexpectedValueException
	 * @return  SessionBagInterface
	 */
	public function getBag($name)
	{
		$name = strtolower($name);

		if (empty($this->bags[$name]))
		{
			throw new \UnexpectedValueException(sprintf('Bag %s not exists', $name));
		}

		return $this->bags[$name];
	}

	/**
	 * setBag
	 *
	 * @param string              $name
	 * @param SessionBagInterface $bag
	 *
	 * @return  Session
	 */
	public function setBag($name, SessionBagInterface $bag)
	{
		$this->bags[strtolower($name)] = $bag;

		if ($this->isActive())
		{
			$this->prepareBagsData(array($name => $bag));
		}

		return $this;
	}

	/**
	 * getFlashBag
	 *
	 * @return  FlashBagInterface
	 */
	public function getFlashBag()
	{
		if (empty($this->bags['flash']))
		{
			$this->bags['flash'] = new FlashBag;
		}

		return $this->bags['flash'];
	}

	/**
	 * setFlashBag
	 *
	 * @param   FlashBagInterface $bag
	 *
	 * @return  Session
	 */
	public function setFlashBag(FlashBagInterface $bag)
	{
		$this->bags['flash'] = $bag;

		return $this;
	}

	/**
	 * Get current state of session
	 *
	 * @return  string  The session state
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get expiration time in minutes
	 *
	 * @return  integer  The session expiration time in minutes
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getExpireTime()
	{
		return $this->expireTime;
	}

	/**
	 * Get a session token, if a token isn't set yet one will be generated.
	 *
	 * Tokens are used to secure forms from spamming attacks. Once a token
	 * has been generated the system will check the post request to see if
	 * it is present, if not it will invalidate the session.
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  The session token
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getToken($forceNew = false)
	{
		$token = $this->get('session.token');

		// Create a token
		if ($token === null || $forceNew)
		{
			$token = $this->createToken(12);

			$this->set('session.token', $token);
		}

		return $token;
	}

	/**
	 * Method to determine if a token exists in the session. If not the
	 * session will be set to expired
	 *
	 * @param   string   $tCheck       Hashed token to be verified
	 * @param   boolean  $forceExpire  If true, expires the session
	 *
	 * @return  boolean
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function hasToken($tCheck, $forceExpire = true)
	{
		// Check if a token exists in the session
		$tStored = $this->get('session.token');

		// Check token
		if (($tStored !== $tCheck))
		{
			if ($forceExpire)
			{
				$this->state = static::STATE_EXPIRED;
			}

			return false;
		}

		return true;
	}

	/**
	 * Retrieve an external iterator.
	 *
	 * @return  \ArrayIterator  Return an ArrayIterator of $_SESSION.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($_SESSION);
	}

	/**
	 * Get session name
	 *
	 * @throws \RuntimeException
	 * @return  string  The session name
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getName()
	{
		if ($this->state === static::STATE_DESTROYED)
		{
			throw new \RuntimeException('Session has been destroyed.');
		}

		return $this->bridge->getName();
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session name
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function getId()
	{
		if ($this->state === static::STATE_DESTROYED)
		{
			return null;
		}

		return $this->bridge->getId();
	}

	/**
	 * Shorthand to check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function isActive()
	{
		return (bool) ($this->state === static::STATE_ACTIVE);
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function isNew()
	{
		$counter = $this->get('session.counter');

		return (bool) ($counter === 1);
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string $name      Name of a variable
	 * @param   mixed  $default   Default value of a variable if not set
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws \RuntimeException
	 * @return  mixed  Value of a variable
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function get($name, $default = null, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			throw new \RuntimeException('Session is not active or expired.');
		}

		return $this->getBag($namespace)->get($name, $default);
	}

	/**
	 * getAll
	 *
	 * @param string $namespace
	 *
	 * @return  mixed
	 *
	 * @throws \RuntimeException
	 */
	public function getAll($namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			throw new \RuntimeException('Session is not active or expired.');
		}

		return $this->getBag($namespace)->all();
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string $name      Name of a variable.
	 * @param   mixed  $value     Value of a variable.
	 * @param   string $namespace Namespace to use, default to 'default'.
	 *
	 * @throws \RuntimeException
	 * @return  Session
	 */
	public function set($name, $value = null, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			throw new \RuntimeException('Session is not active. Now is: ' . $this->state);
		}

		$this->getBag($namespace)->set($name, $value);

		return $this;
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws \RuntimeException
	 * @return  boolean  True if the variable exists
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function has($name, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			throw new \RuntimeException('Session is not active.');
		}

		return $this->getBag($namespace)->has($name);
	}

	/**
	 * Unset data from the session store
	 *
	 * @param   string $name      Name of variable
	 * @param   string $namespace Namespace to use, default to 'default'
	 *
	 * @throws \RuntimeException
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function clear($name, $namespace = 'default')
	{
		if ($this->state !== static::STATE_ACTIVE && $this->state !== static::STATE_EXPIRED)
		{
			throw new \RuntimeException('Session is not active.');
		}

		$this->getBag($namespace)->set($name, null);

		return $this;
	}

	/**
	 * addFlash
	 *
	 * @param array|string $msg
	 * @param string       $type
	 *
	 * @return  Session
	 */
	public function addFlash($msg, $type = 'info')
	{
		$this->getFlashBag()->add($msg, $type);

		return $this;
	}

	/**
	 * Start a session.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function start()
	{
		if ($this->state === static::STATE_ACTIVE)
		{
			return;
		}

		$this->doStart();

		$this->state = static::STATE_ACTIVE;

		// Initialise the session
		$this->setCounter();
		$this->setTimers();

		// Perform security checks
		$this->validate();
	}

	/**
	 * Start a session.
	 *
	 * Creates a session (or resumes the current one based on the state of the session)
	 *
	 * @return  boolean  true on success
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function doStart()
	{
		// Start session if not started
		if ($this->state === static::STATE_RESTART)
		{
			$this->bridge->regenerate(true);
		}
		else
		{
			$sessionName = $this->bridge->getName();

			$cookie = $this->getCookie();

			// If cookie do not have session id, try to get it from http queries.
			if (empty($cookie[$sessionName]))
			{
				$sessionClean = isset($_GET[$sessionName]) ? $_GET[$sessionName] :  false;

				if ($sessionClean)
				{
					$this->bridge->getId($sessionClean);
					setcookie($sessionName, '', time() - 3600);
					$cookie[$sessionName] = '';
				}
			}
		}

		$this->bridge->start();

		$this->prepareBagsData($this->bags);

		return true;
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the $_SESSION variable and destroys all of the data associated
	 * with the current session in its storage (file or DB). It forces new session to be
	 * started after this method is called. It does not unset the session cookie.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   {DEPLOY_VERSION}
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->state === static::STATE_DESTROYED)
		{
			return true;
		}

		/*
		 * In order to kill the session altogether, such as to log the user out, the session id
		 * must also be unset. If a cookie is used to propagate the session id (default behavior),
		 * then the session cookie must be deleted.
		 */
		if (isset($_COOKIE[$this->bridge->getName()]))
		{
			setcookie($this->bridge->getName(), '', time() - 42000, $this->cookiePath, $this->cookieDomain);
		}

		$this->bridge->clear();

		$this->state = static::STATE_DESTROYED;

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @throws \RuntimeException
	 * @return  boolean  True on success
	 *
	 * @see     destroy
	 * @since   {DEPLOY_VERSION}
	 */
	public function restart()
	{
		$this->destroy();

		if ($this->state !== static::STATE_DESTROYED)
		{
			throw new \RuntimeException('Session not destroyed, cannot restart.');
		}

		// Re-register the session handler after a session has been destroyed, to avoid PHP bug
		$this->registerHandler();

		$this->state = static::STATE_RESTART;

		// Regenerate session id
		$this->bridge->regenerate(true);
		$this->doStart();
		$this->state = static::STATE_ACTIVE;

		$this->validate();
		$this->setCounter();

		return true;
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @throws \RuntimeException
	 * @return  boolean $result true on success
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	public function fork()
	{
		if ($this->state !== static::STATE_ACTIVE)
		{
			throw new \RuntimeException('Session is not active.');
		}

		// Keep session config
		$cookie = $this->bridge->getCookieParams();

		// Kill session
		$this->bridge->clear();

		// Re-register the session store after a session has been destroyed, to avoid PHP bug
		$this->registerHandler();

		// Restore config
		$this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);

		// Restart session with new id
		$this->bridge->regenerate(true);

		return true;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call close(), but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   {DEPLOY_VERSION}
	 */
	public function close()
	{
		$this->bridge->save();
	}

	/**
	 * getCookieParams
	 *
	 * @return  array
	 */
	protected function getCookieParams()
	{
		return $this->bridge->getCookieParams();
	}

	/**
	 * Set session cookie parameters, this method should call before session started.
	 *
	 * @return  void
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function setCookieParams()
	{
		$cookie = $this->getCookieParams();

		if ($this->forceSSL)
		{
			$cookie['secure'] = true;
		}

		if ($this->cookieDomain)
		{
			$cookie['domain'] = $this->cookieDomain;
		}

		if ($this->cookiePath)
		{
			$cookie['path'] = $this->cookiePath;
		}

		$this->bridge->setCookieParams($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'], true);
	}

	/**
	 * Create a token-string
	 *
	 * @param   integer  $length  Length of string
	 *
	 * @return  string  Generated token
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function createToken($length = 32)
	{
		static $chars = '0123456789abcdef';
		$max = strlen($chars) - 1;
		$token = '';
		$name = $this->bridge->getName();

		for ($i = 0; $i < $length; ++$i)
		{
			$token .= $chars[(rand(0, $max))];
		}

		return md5($token . $name);
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  static Return self to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function setCounter()
	{
		$counter = $this->get('session.counter', 0);

		++$counter;

		$this->set('session.counter', $counter);

		return $this;
	}

	/**
	 * Set the session timers
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function setTimers()
	{
		if (!$this->has('session.timer.start'))
		{
			$start = time();

			$this->set('session.timer.start', $start);
			$this->set('session.timer.last', $start);
			$this->set('session.timer.now', $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return $this;
	}

	/**
	 * Set additional session options
	 *
	 * @param   array  $options  List of parameter
	 *
	 * @return  boolean  True on success
	 *
	 * @since   {DEPLOY_VERSION}
	 */
	protected function setOptions(array $options)
	{
		// Set name
		if (isset($options['name']))
		{
			$this->bridge->setName(md5($options['name']));
		}

		// Set id
		if (isset($options['id']))
		{
			$this->bridge->setId($options['id']);
		}

		// Set expire time
		if (isset($options['expire_time']))
		{
			$this->expireTime = $options['expire_time'];
		}

		// Get security options
		if (isset($options['security']))
		{
			$this->security = explode(',', $options['security']);
		}

		if (isset($options['force_ssl']))
		{
			$this->forceSSL = (bool) $options['force_ssl'];
		}

		if (isset($options['cookie_domain']))
		{
			$this->cookieDomain = $options['cookie_domain'];
		}

		if (isset($options['cookie_path']))
		{
			$this->cookiePath = $options['cookie_path'];
		}

		// Sync the session maxlifetime
		ini_set('session.gc_maxlifetime', $this->expireTime * 60);

		return true;
	}

	/**
	 * Do some checks for security reason
	 *
	 * - timeout check (expire)
	 * - ip-fixiation
	 * - browser-fixiation
	 *
	 * If one check failed, session data has to be cleaned.
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  boolean  True on success
	 *
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   {DEPLOY_VERSION}
	 */
	protected function validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->state = static::STATE_ACTIVE;

			$this->set('session.client.address', null);
			$this->set('session.client.forwarded', null);
			$this->set('session.client.browser', null);
			$this->set('session.token', null);
		}

		// Check if session has expired
		if ($this->expireTime)
		{
			$curTime = $this->get('session.timer.now', 0);
			$maxTime = $this->get('session.timer.last', 0) + ($this->expireTime * 60);

			// Empty session variables
			if ($maxTime < $curTime)
			{
				$this->state = static::STATE_EXPIRED;

				return false;
			}
		}

		// Record proxy forwarded for in the session in case we need it later
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$this->set('session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR']);
		}

		// Check for client address
		if (in_array('fix_adress', $this->security) && isset($_SERVER['REMOTE_ADDR']))
		{
			$ip = $this->get('session.client.address');

			if ($ip === null)
			{
				$this->set('session.client.address', $_SERVER['REMOTE_ADDR']);
			}
			elseif ($_SERVER['REMOTE_ADDR'] !== $ip)
			{
				$this->state = static::STATE_ERROR;

				return false;
			}
		}

		// Check for clients browser
		if (in_array('fix_browser', $this->security) && isset($_SERVER['HTTP_USER_AGENT']))
		{
			$browser = $this->get('session.client.browser');

			if ($browser === null)
			{
				$this->set('session.client.browser', $_SERVER['HTTP_USER_AGENT']);
			}
			elseif ($_SERVER['HTTP_USER_AGENT'] !== $browser)
			{
				// @todo remove code: 				$this->_state	=	'error';
				// @todo remove code: 				return false;
			}
		}

		return true;
	}

	/**
	 * preapreBagsData
	 *
	 * @param SessionBagInterface[] $bags
	 *
	 * @return  Session
	 */
	protected function prepareBagsData(array $bags)
	{
		foreach ($bags as $name => $bag)
		{
			$ns = '_' . strtolower($name);

			$session = &$_SESSION;

			if (!isset($session[$ns]) || !is_array($session[$ns]))
			{
				$session[$ns] = array();
			}

			$bag->setData($session[$ns]);
		}

		return $this;
	}
}