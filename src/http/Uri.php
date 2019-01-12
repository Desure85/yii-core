<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

use Psr\Http\Message\UriInterface;
use yii\base\BaseObject;
use yii\base\ErrorHandler;
use yii\exceptions\InvalidArgumentException;

/**
 * Uri represents an URI.
 *
 * ```php
 * $uri = (new Uri())
 *     ->withScheme('http')
 *     ->withUserInfo('username', 'password')
 *     ->withHost('example.com')
 *     ->withPort(9090)
 *     ->withPath('/content/path')
 *     ->withQuery('foo=some')
 *     ->withFragment('anchor');
 * ```
 *
 * @property string $scheme the scheme component of the URI.
 * @property string $user
 * @property string $password
 * @property string $host the hostname to be used.
 * @property int|null $port port number.
 * @property string $path the path component of the URI
 * @property string|array $query the query string or array of query parameters.
 * @property string $fragment URI fragment.
 * @property string $authority the authority component of the URI. This property is read-only.
 * @property string $userInfo the user information component of the URI. This property is read-only.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 3.0.0
 */
class Uri extends BaseObject implements UriInterface
{
    /**
     * @var string URI complete string.
     */
    private $_string;
    /**
     * @var array URI components.
     */
    private $_components;
    /**
     * @var array scheme default ports in format: `[scheme => port]`
     */
    private static $defaultPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];


    /**
     * @return string URI string representation.
     */
    public function getString(): string
    {
        if ($this->_string !== null) {
            return $this->_string;
        }
        if ($this->_components === null) {
            return '';
        }
        return $this->composeUri($this->_components);
    }

    /**
     * @param string $string URI full string.
     */
    public static function fromString(string $string)
    {
        $uri = new self();
        $uri->_string = $string;
        $uri->_components = null;
        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->getComponent('scheme');
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        if ($this->getScheme() === $scheme) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('scheme', $scheme);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        return $this->composeAuthority($this->getComponents());
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return $this->composeUserInfo($this->getComponents());
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->getComponent('host', '');
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        if ($this->getHost() === $host) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('host', $host);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->getComponent('port');
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        if ($this->getPort() === $port) {
            return $this;
        }

        $newInstance = clone $this;

        if ($port !== null && !\is_int($port)) {
            throw new InvalidArgumentException('URI port must be an integer.');
        }
        $newInstance->setComponent('port', $port);

        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->getComponent('path', '');
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        if ($this->getPath() === $path) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('path', $path);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->getComponent('query', '');
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        if ($this->getQuery() === $query) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('query', $query);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->getComponent('fragment', '');
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        if ($this->getFragment() === $fragment) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('fragment', $fragment);
        return $newInstance;
    }

    /**
     * @return string the user name to use for authority.
     */
    public function getUser(): string
    {
        return $this->getComponent('user', '');
    }

    /**
     * @return string password associated with [[user]].
     */
    public function getPassword(): string
    {
        return $this->getComponent('pass', '');
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null): self
    {
        $userInfo = $user;
        if ($password !== null) {
            $userInfo .= ':' . $password;
        }

        if ($userInfo === $this->composeUserInfo($this->getComponents())) {
            return $this;
        }

        $newInstance = clone $this;
        $newInstance->setComponent('user', $user);
        $newInstance->setComponent('pass', $password);
        return $newInstance;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->getString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }

    /**
     * Sets up particular URI component.
     * @param string $name URI component name.
     * @param mixed $value URI component value.
     */
    protected function setComponent(string $name, $value): void
    {
        if ($this->_string !== null) {
            $this->_components = $this->parseUri($this->_string);
        }
        $this->_components[$name] = $value;
        $this->_string = null;
    }

    /**
     * @param string $name URI component name.
     * @param mixed $default default value, which should be returned in case component is not exist.
     * @return mixed URI component value.
     */
    protected function getComponent(string $name, $default = null)
    {
        $components = $this->getComponents();
        if (isset($components[$name])) {
            return $components[$name];
        }
        return $default;
    }

    /**
     * Returns URI components for this instance as an associative array.
     * @return array URI components in format: `[name => value]`
     */
    protected function getComponents(): array
    {
        if ($this->_components === null) {
            if ($this->_string === null) {
                return [];
            }
            $this->_components = $this->parseUri($this->_string);
        }
        return $this->_components;
    }

    /**
     * Parses a URI and returns an associative array containing any of the various components of the URI
     * that are present.
     * @param string $uri the URI string to parse.
     * @return array URI components.
     */
    protected function parseUri(string $uri): array
    {
        $components = parse_url($uri);
        if ($components === false) {
            throw new InvalidArgumentException("URI string '{$uri}' is not a valid URI.");
        }
        return $components;
    }

    /**
     * Composes URI string from given components.
     * @param array $components URI components.
     * @return string URI full string.
     */
    protected function composeUri(array $components): string
    {
        $uri = '';

        $scheme = empty($components['scheme']) ? 'http' : $components['scheme'];
        if ($scheme !== '') {
            $uri .= $scheme . ':';
        }

        $authority = $this->composeAuthority($components);

        if ($authority !== '' || $scheme === 'file') {
            // authority separator is added even when the authority is missing/empty for the "file" scheme
            // while `file:///myfile` and `file:/myfile` are equivalent according to RFC 3986, `file:///` is more common
            // PHP functions and Chrome, for example, use this format
            $uri .= '//' . $authority;
        }

        if (!empty($components['path'])) {
            $uri .= $components['path'];
        }

        if (!empty($components['query'])) {
            $uri .= '?' . $components['query'];
        }

        if (!empty($components['fragment'])) {
            $uri .= '#' . $components['fragment'];
        }

        return $uri;
    }

    /**
     * @param array $components URI components.
     * @return string user info string.
     */
    protected function composeUserInfo(array $components): string
    {
        $userInfo = '';
        if (!empty($components['user'])) {
            $userInfo .= $components['user'];
        }
        if (!empty($components['pass'])) {
            $userInfo .= ':' . $components['pass'];
        }
        return $userInfo;
    }

    /**
     * @param array $components URI components.
     * @return string authority string.
     */
    protected function composeAuthority(array $components): string
    {
        $authority = '';

        $scheme = empty($components['scheme']) ? '' : $components['scheme'];

        if (empty($components['host'])) {
            if (\in_array($scheme, ['http', 'https'], true)) {
                $authority = 'localhost';
            }
        } else {
            $authority = $components['host'];
        }
        if (!empty($components['port']) && !$this->isDefaultPort($scheme, $components['port'])) {
            $authority .= ':' . $components['port'];
        }

        $userInfo = $this->composeUserInfo($components);
        if ($userInfo !== '') {
            $authority = $userInfo . '@' . $authority;
        }

        return $authority;
    }

    /**
     * Checks whether specified port is default one for the specified scheme.
     * @param string $scheme scheme.
     * @param int $port port number.
     * @return bool whether specified port is default for specified scheme
     */
    protected function isDefaultPort(string $scheme, int $port): bool
    {
        if (!isset(self::$defaultPorts[$scheme])) {
            return false;
        }
        return self::$defaultPorts[$scheme] === $port;
    }
}
