<?php

declare(strict_types=1);

\Setting\Route\Function\Controllers\Admin\AdminAuth::auth();

\App\Models\Network\Network::onRedirect('/admin');
