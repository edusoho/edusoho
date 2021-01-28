# Changelog
## 0.1.0

### Features
- Initial Release

## 0.1.1

### Features
- Added String::chr for suppporting Unicode Characters

## 0.2.0

### Changes
- Renamed String class in Text class for supporting PHP7

## 0.2.1

### Features
- Added XMLReader from PHPWord

## 0.2.2

### BugFix
- Fixed "Class 'PhpOffice\Common\ZipArchive' not found in /src/Common/XMLReader.php on line 54"

## 0.2.3

### Features
- Added missing features for supporting PHPWord

## 0.2.4

### Changes
- XMLWriter : Refactoring for improving performances

## 0.2.5

### Features
- Added Zip Adapters (PclZip & ZipArchive)

## 0.2.6

### Changes
- `\PhpOffice\Common\Text::utf8ToUnicode()` became `public`.

## 0.2.7

### Features
- Added `\PhpOffice\Common\File::fileGetContents()` (with support of zip://)
- Added Support for PHP 7.1

## 0.2.8

### Features
- Added possibility to register namespaces to DOMXpath
- Added Utility to get an Office compatible hash of a password
- Write attribute's value of type float independently of locale

## 0.2.9
- Fix XML Entity injection vulnerability