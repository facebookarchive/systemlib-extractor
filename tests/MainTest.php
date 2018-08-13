<?hh

namespace HHVM\SystemlibExtractor;
use function Facebook\FBExpect\expect;

class MainTest extends \PHPUnit_Framework_TestCase {
  public function testContainsSystemlib(): void {
    expect((new SystemlibExtractor())->getSectionNames())->toContain(
      'systemlib',
    );
  }

  public function testContainsExtensions(): void {
    expect(
      (new SystemlibExtractor())
        ->getSectionNames()
        ->filter($name ==> \substr($name, 0, 3) === 'ext'),
    )->toNotBeEmpty();
  }

  public function testSystemlibDoesNotContainHHAS(): void {
    $bytes = (new SystemlibExtractor())->getSectionContents('systemlib');
    expect(\strpos($bytes, '<?hhas'))->toBeSame(false);
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

    expect(\substr($bytes, 0, 4))->toBeSame('<?hh');

    expect(\strlen($bytes))->toBeGreaterThan($min_size);
  }
}
