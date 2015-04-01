
The following is required for this module to work

-----------------------------------------------------------------------------

Back Office => Preferences => Performance

    under "CCC (Combine, Compress and Cache)"
    - option "Compress inline JavaScript in HTML"
      must be set to "Keep inline JavaScript in HTML as original"

-----------------------------------------------------------------------------

404 error pages are tracked in the same way prestashop module "pagesnotfound"
do it this means that all 404 http errors must be redirected to 404.php using
a .htaccess file like this

    # Catch 404 errors
    ErrorDocument 404 /shop-folder/404.php

this rule is generated in Back Office => Tools => Generators
	