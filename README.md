Behind
======

Behind is a php toolbox for environments of your php-application.

UnitTests and Code Analysis
---------------------------
master:
[![Build Status](https://travis-ci.org/glady/Behind.png?branch=master)](https://travis-ci.org/glady/Behind)
[![Code Climate](https://codeclimate.com/github/glady/Behind/badges/gpa.svg)](https://codeclimate.com/github/glady/Behind)
[![Test Coverage](https://codeclimate.com/github/glady/Behind/badges/coverage.svg)](https://codeclimate.com/github/glady/Behind)
v0.1.x:
[![Build Status](https://travis-ci.org/glady/Behind.png?branch=v0.1.x)](https://travis-ci.org/glady/Behind/tree/v0.1.x)

Milestones
----------

1. [DONE] *ClassLoader*-Instance which can be registered as php-autoloader
2. [DONE] *PackageHandler* as extension for ClassLoader
3. [DONE] *ClassMapGenerator* for automatic generation of class maps thats can be used by ClassLoader
4. [IN WORK] class loading of installed composer dependencies (classmap, psr-0, psr-4)
5. *TestFramework* for simple test cases needed to test contained tools
6. *ErrorHandler* and a class for *Debug*-Information
6. *Logger* classes for different targets

Basic Usage
-----------

The target is to create *independent* classes, not a FrameWork. You can use one single file (e.g. ClassLoader) for single require within your code. Only extensions should require their base class(es).

Two usages are possible:

1. use single files (possibly with their base classes)
2. use composer package for full toolbox
