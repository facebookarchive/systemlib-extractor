<?hh // strict
/*
 *  Copyright (c) 2015, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace HHVM\SystemlibExtractor;

class SystemlibExtractor {
  private string $readelf;

  public function __construct(
    private string $binaryPath = \PHP_BINARY,
    ?string $readelf = null,
  ) {
    if ($readelf === null) {
      $readelf = \shell_exec('which readelf');
      if ($readelf !== null) {
        $readelf = \trim($readelf);
      }
    }
    if (!\is_executable($readelf)) {
      throw new ReadelfNotFoundException(
        "Could not find `readelf` - install it, and put it into \$PATH or ".
        "specify full path"
      );
    }
    $this->readelf = $readelf;
  }

  <<__Memoize>>
  public function getSectionNames(): ImmVector<string> {
    $parts = Vector {
      $this->readelf,
      '--section-headers',
      $this->binaryPath,
    };
    $sections = \shell_exec(\implode(' ', $parts->map($x ==> \escapeshellarg($x))));
    $sections = (new Vector(\explode("\n", $sections)))
      ->filter($line ==> \strpos($line, 'PROGBITS') !== false)
      ->map($line ==> \preg_split('/\s+/', $line)[2])
      ->filter($name ==> \preg_match('/^(ext\.|systemlib$)/', $name) !== 0);
    return $sections->immutable();
  }

  <<__Memoize>>
  public function getSectionContents(string $name): string {
    invariant(
      $this->getSectionNames()->toSet()->contains($name),
      '%s is not a section name',
      $name,
    );

    $cmd = Vector {
      $this->readelf,
      '--hex-dump', // --string-dump does some escaping :(
      $name,
      '--wide',
      $this->binaryPath,
    };
    $raw = \shell_exec(\implode(' ', $cmd->map($x ==>\escapeshellarg($x))));
    $bytes = (new Vector(\explode("\n", $raw)))
      // 0xADDR deadbeef deadbeef deadbeef deadbeef <?hh foo ba
      ->filter($line ==> \strpos($line, '  0x') === 0)
      // deadbeefdeadbeefdeadbeefdeadbeef
      ->map($line ==> \implode('', \array_slice(\explode(' ', \trim($line)), 1, 4)))
      ->map($hex ==> \hex2bin($hex));
    $bytes = \implode('', $bytes);

    // array_map() and a few other functions in SystemLib are implemented in
    // HH ASsembly instead of Hack/PHP. These need to be ignored, and are always
    // at the end, after this marker.
    //
    // This approach is used in HPHP::systemlib_split() when HHVM laods
    // systemlib.
    $bytes = \explode("\n<?hhas\n", $bytes)[0];

    return $bytes;
  }
}
