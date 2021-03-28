# Changelog  
## 2.3.2  
Fixed:
* DataTables paging and search not updating picker events
* Breadcrumbs had Dropbox path where it shouldn't
* Fix multi add checkbox logic

## 2.3.1  
Fixed:  
* Filemanager `index.blade.php` styling fixes

## 2.3.0  
Changed: 
* Required L8.x - PHP 7.3+

## 2.2.1  
Fixed:  
* Filemanager `index.blade.php` styling fixes

## 2.2.0  
Changed:  
* Required L6.x/L7.x - PHP 7.2.5+
* Updated to bootstrap 4
* Updated to jQuery 3
* Changed js and css `@section` to `@stack` and `@push` 
* Updated to Fontawesome 5

## 2.2.2
Fixed:
* DataTables paging and search not updating picker events
* Breadcrumbs had Dropbox path where it shouldn't

## 2.2.1  
Added:  
* Config setting to choose `bigIncrements` and `bigInteger` usage  

Changed
* Use `bigIncrements` and `bigInteger` by default in migrations

##2.1.0  
Added: 
* Dropbox and OneDrive integrations

Changed:  
* Requires L5.6+/PHP7.1+

## 2.0.0  
Added: 
* Multiple files select option

Changed:  
* Now using localstorage to communicate te data back to the main view

## 1.1.1
Fix:
* When uploading and renaming a file, check if the file extention exists and add it again if it's missing.

## 1.1.0
Added:
* Width and height getters

## 1.0.2
Fix:
* View publish path

## 1.0.1
Added:
* Some missing stuff in phpdocs
* Better disk config integration

Fix:
* CKEditor integration

## 1.0.0
Added:
* Laravel 5.5 compatible

Fix:
* Simplified config