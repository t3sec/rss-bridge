### RSS-Bridge Development Guidelines

### Build/Configuration Instructions
RSS-Bridge is a PHP web application that can often be deployed by simply unzipping it into a web-accessible folder.

- **Dependencies**: Managed via Composer. Run `composer install` to install development dependencies.
- **Local Development**: You can start a minimal development environment using PHP's built-in server:
  ```bash
  php -S 127.0.0.1:9001
  ```
- **Configuration**: 
  - Copy `config.default.ini.php` to `config.ini.php` to customize your instance.
  - Useful settings include `enabled_bridges[] = *` to enable all bridges during development.
- **Docker**: Docker and Docker Compose support is available for containerized development and deployment.

### Testing Information
RSS-Bridge uses PHPUnit for testing.

- **Running all tests**:
  ```bash
  ./vendor/bin/phpunit
  ```
- **Running a specific test**:
  ```bash
  ./vendor/bin/phpunit tests/UrlTest.php
  # Or using filter
  ./vendor/bin/phpunit --filter UrlTest
  ```
- **Adding new tests**: 
  - Place new test files in the `tests/` directory.
  - Test classes should extend `PHPUnit\Framework\TestCase` and reside in the `RssBridge\Tests` namespace.
- **Example Test**:
  The following simple test demonstrates how to create a test case:
  ```php
  <?php
  declare(strict_types=1);
  namespace RssBridge\Tests;
  use PHPUnit\Framework\TestCase;
  final class ExampleTest extends TestCase {
      public function testSimpleAssertion() {
          $this->assertTrue(true);
      }
  }
  ```

### Additional Development Information

#### Code Style
The project follows **PSR-12** with some specific modifications defined in `phpcs.xml`.
- **Strict Types**: All new PHP files MUST start with `declare(strict_types=1);`.
- **Linting**: Run the linter to ensure code style compliance:
  ```bash
  ./vendor/bin/phpcs --standard=phpcs.xml --warning-severity=0 --extensions=php -p ./
  ```
- **Arrays**: Use short array syntax `[]`.
- **Strings**: Prefer single quotes `'` unless double quotes are necessary (e.g., for variable interpolation).

#### Bridge Development
- Bridges must extend `BridgeAbstract`.
- Key constants to define: `NAME`, `URI`, `DESCRIPTION`, `MAINTAINER`, `PARAMETERS`, `CACHE_TIMEOUT`.
- The main logic resides in the `collectData()` method, where you populate `$this->items`.
- Use helper functions like `getSimpleHTMLDOM($url)` or `getSimpleHTMLDOMCached($url, $timeout)` for fetching and parsing HTML.
- **Common Helper Functions**:
  - `urljoin($base, $rel)`: Safely resolve relative URLs.
  - `defaultLinkTo($content, $server)`: Fix relative links and image sources in HTML content.
  - `extractFromDelimiters($string, $start, $end)`: Extract data from strings between delimiters (useful for JSON-LD or scripts).
  - `sanitize($content)`: Clean up HTML content.
- **Feed Item Structure**: Bridges should produce items with keys such as `uri`, `title`, `timestamp`, `author`, `content`, `enclosures`, and `categories`.

#### FeedExpander Development
Bridges can extend `FeedExpander` instead of `BridgeAbstract` to enrich existing RSS or Atom feeds.
- Use `$this->collectExpandableDatas($url)` in `collectData()`.
- Override `parseItem(array $item)` to modify or add data to each item (e.g., fetching the full article content).

#### Best Practices
- **Error Handling**: Use `throwClientException($message)` for user-side errors or `throwServerErrorException($message)` for server-side issues instead of generic exceptions.
- **Type Hinting**: Use return type hints (e.g., `: void`, `: array`) to align with strict typing.
- **Final Classes**: Use `final class` for bridge definitions if they are not intended to be extended.

#### Debugging
- Enable debug mode in `config.ini.php` by setting `[error] display_errors = true`.
- Errors can be reported as feed items (default) or as HTTP error messages.
