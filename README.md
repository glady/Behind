Behind
======

Behind is a php toolbox for environments of your php-application.

UnitTests and Code Analysis
---------------------------
master:
[![Build Status](https://travis-ci.org/glady/Behind.png?branch=master)](https://travis-ci.org/glady/Behind)
[![Code Climate](https://codeclimate.com/github/glady/Behind/badges/gpa.svg)](https://codeclimate.com/github/glady/Behind)
[![Test Coverage](https://codeclimate.com/github/glady/Behind/badges/coverage.svg)](https://codeclimate.com/github/glady/Behind)

Milestones
----------

1.1. [DONE] *ClassLoader*-Instance which can be registered as php-autoloader
1.2. [DONE] *PackageHandler* as extension for ClassLoader
1.3. [DONE] *ClassMapGenerator* for automatic generation of class maps thats can be used by ClassLoader
2. *TestFramework* for simple test cases needed to test contained tools
3. *ErrorHandler* and a class for *Debug*-Information

Basic Usage
-----------

The target is to create *independent* classes, not a FrameWork. You can use one single file (e.g. ClassLoader) for single require within your code. Only extensions should require their base class(es).

Two usages are possible:
1. use single files (possibly with their base classes)
2. use composer package for full toolbox

When using composer package, a class loader instance is registered for loading classes of this toolbox. This classloader can be modified and extended by getting instance by "\glady\Behind::getClassLoader()".
