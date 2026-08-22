#!/bin/bash

#
# -------------------------------------------------------------------------
# vip plugin for GLPI
# Copyright (C) 2022-2026 by the vip Development Team.
#
# https://github.com/pluginsGLPI/vip
# -------------------------------------------------------------------------
#
# LICENSE
#
# This file is part of vip.
#
# vip is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# vip is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with vip. If not, see <http://www.gnu.org/licenses/>.
# --------------------------------------------------------------------------
#

find . -name '*.php' > php_files.list

# Extraction avec xgettext
xgettext --files-from=php_files.list \
  --copyright-holder='VIP Development Team' \
  --package-name='VIP plugin' \
  -o locales/glpi.pot \
  -L PHP \
  --add-comments=TRANS \
  --from-code=UTF-8 \
  --force-po \
  --keyword=_n:1,2,4t \
  --keyword=__s:1,2t \
  --keyword=__:1,2t \
  --keyword=_e:1,2t \
  --keyword=_x:1c,2,3t \
  --keyword=_ex:1c,2,3t \
  --keyword=_nx:1c,2,3,5t \
  --keyword=_sx:1c,2,3t

# Nettoyage
rm php_files.list

