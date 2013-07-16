--    POS-tech is a point of sales software
--    Copyright (C) 2012 SARL SCOP Scil
--    http://trac.scil.coop/pos-tech
--
--    This file is part of POS-Tech
--
--    POS-tech is free software: you can redistribute it and/or modify
--    it under the terms of the GNU General Public License as published by
--    the Free Software Foundation, either version 3 of the License, or
--    (at your option) any later version.
--
--    POS-tech is distributed in the hope that it will be useful,
--    but WITHOUT ANY WARRANTY; without even the implied warranty of
--    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
--    GNU General Public License for more details.
--
--    You should have received a copy of the GNU General Public License
--    along with POS-tech. If not, see <http://www.gnu.org/licenses/>.

-- Database upgrade script for MYSQL

-- db v3 - v4

-- final script
UPDATE CATEGORIES SET NAME = 'Compositions' WHERE ID = '0';
UPDATE CATEGORIES SET NAME = 'Pre-payment' WHERE ID = '-1';

UPDATE CURRENCIES SET NAME = 'Pound', SYMBOL = '£', DECIMALSEP = '.', THOUSANDSSEP = '', FORMAT = '$#0.00' WHERE ID = '1';

