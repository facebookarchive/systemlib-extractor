<?hh

namespace HHVM\SystemlibExtractor;

class MainTest extends \PHPUnit_Framework_TestCase {
  public function testContainsSystemlib(): void {
    $this->assertContains(
      'systemlib',
      (new SystemlibExtractor())->getSectionNames(),
    );
  }

  public function testContainsExtensions(): void {
    $this->assertNotEmpty(
      (new SystemlibExtractor())
      ->getSectionNames()
      ->filter($name ==> substr($name, 0, 3) === 'ext'),
    );
  }

  public function testSystemlibDoesNotContainHHAS(): void {
    $bytes = (new SystemlibExtractor())->getSectionContents('systemlib');
    $this->assertSame(
      false,
      strpos($bytes, '<?hhas'),
    );
  }

  public function sectionsProvider(): array<array<mixed>> {
    // Minimum sizes in bytes. Arbitrary, but better than nothing.
    $min_systemlib = 1024 * 100;
    $min_ext = 128;
    return
      (new SystemlibExtractor())
      ->getSectionNames()
      ->map($x ==> [$x, $x === 'systemlib' ? $min_systemlib : $min_ext ])
      ->toArray();
  }

  /**
   * @dataProvider sectionsProvider
   */
  public function testCanExtractSection(string $name, int $min_size): void {
    $bytes = (new SystemlibExtractor())->getSectionContents($name);

    $this->assertSame(
      '<?hh',
      substr($bytes, 0, 4),
    );

    $this->assertGreaterThan(
      $min_size,
      strlen($bytes),
    );
  }
}
