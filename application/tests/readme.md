Instructions
------------

All you need to do is check out this repository as a submodule of your CodeIgniter
2.1.0 project. The recommended location is into a directory called `tests` inside
your application directory, wherever that may be. For me (YMMV), that means I'd run the
command from my CI project directory:

```bash
$ git submodule add git://github.com/redvel/PHPUnit-for-CodeIgniter.git application/tests
```

To test and make sure everything is set up OK, go to your new tests directory on
the command line and enter `phpunit`. If all is OK, it should show that you are
failing one test.

OPTIONAL: I like to add a symlink to `application/tests/phpunit.xml` inside my
project's root directory, so I can just call phpunit from the command line right
there at the project root. If you do this, there's no need to update any paths or
anything from inside the phpunit XML file; PHPUnit is smart enough to interpret the
symlink and make sure all the references are correct. So again using my own environment
as an example, to do this I would run the following from my project directory:

```bash
$ ln -s application/tests/phpunit.xml phpunit.xml
```

Compatibility
-------------

The one-and-only environment I have ever tested this on is:

 *  Mac OS X Lion
 *  PHP 5.3.6 cli
 *  PHPUnit 3.5.14
 *  CodeIgniter 2.1.0

Please note that you'll almost certainly have database problems if the command line
PHP executable is not the same as the one you're using on your development server.
For instance, if you use MAMP as your development server, but your CLI PHP executable
is at `/usr/bin/php`, this will likely cause a database issue.

The right way to do this is to configure your PATH to use the same PHP (and Pear,
PHPUnit, etc.) executables as exist in your development server's environment.


License
-------

Original code and modifications to CodeIgniter Source are (c) 2012 Redvel Software LLC.

Permission is hereby granted, free of charge, to any person obtaining a copy of this 
software and associated documentation files (the "Software"), to deal in the Software 
without restriction, including without limitation the rights to use, copy, modify, 
merge, publish, distribute, sublicense, and/or sell copies of the Software, and to 
permit persons to whom the Software is furnished to do so, subject to the following 
conditions:

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software not used in production.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT 
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF 
CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE 
OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

