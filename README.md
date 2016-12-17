Behind
======

Behind is a php toolbox with helper classes of your php-application.

UnitTests and Code Analysis
---------------------------
master:
[![Build Status](https://travis-ci.org/glady/Behind.png?branch=master)](https://travis-ci.org/glady/Behind)
[![Code Climate](https://codeclimate.com/github/glady/Behind/badges/gpa.svg)](https://codeclimate.com/github/glady/Behind)
[![Test Coverage](https://codeclimate.com/github/glady/Behind/badges/coverage.svg)](https://codeclimate.com/github/glady/Behind)

v0.1.x:
[![Build Status](https://travis-ci.org/glady/Behind.png?branch=v0.1.x)](https://travis-ci.org/glady/Behind/branches)

Milestones
----------

1. [DONE] *ClassLoader*-Instance which can be registered as php-autoloader
2. [DONE] *PackageHandler* as extension for ClassLoader
3. [DONE] *ClassMapGenerator* for automatic generation of class maps thats can be used by ClassLoader
4. [IN WORK] class loading of installed composer dependencies (classmap, psr-0, psr-4, function-includes)
   - [#3] prototype for function-loader
5. [PLANNED] *DependencyInjectionContainer* [#7]
6. *TestFramework* for simple test cases needed to test contained tools
7. *ErrorHandler* and a class for *Debug*-Information
8. *Logger* classes for different targets
