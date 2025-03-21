<?php

/**
 * Mail-in-a-Box Driver
 *
 * Driver that adds functionality to change the user password via the
 * API endpoint of Mail-in-a-Box (https://mailinabox.email/).
 *
 * For installation instructions please read the README file. It requires
 * following parameters in configuration:
 *
 * - password_miab_username - name of the admin user used to access api
 * - password_miab_password - password of the admin user used to access api
 * - password_miab_url - the url to the control panel of Mail-in-a-Box
 *
 * @version 1.0
 *
 * @author Alexey Shtokalo <alexey@shtokalo.net>
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

class rcube_miab_password
{
    public function save($currpass, $newpass, $username)
    {
        $config = rcmail::get_instance()->config;
        $host = rtrim($config->get('password_miab_url'), '/') . '/mail/users/password';

        try {
            $client = password::get_http_client();

            $request = [
                'form_params' => [
                    'email' => $username,
                    'password' => $newpass,
                ],
                'auth' => [
                    $config->get('password_miab_user') ?: $username,
                    $config->get('password_miab_pass') ?: $currpass,
                ],
            ];

            $response = $client->post($host, $request);
            $result = $response->getBody();

            if ($response->getStatusCode() == 200 && trim($result) === 'OK') {
                return PASSWORD_SUCCESS;
            }
        } catch (Exception $e) {
            $result = $e->getMessage();
        }

        rcube::raise_error("Password plugin: Unable to change password. {$result}", true);

        return PASSWORD_ERROR;
    }
}
