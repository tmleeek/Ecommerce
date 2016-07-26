#!/bin/bash
#
DBMage="lunddev_mage"
mageFile="../sql/lunddev_mage.20160620.1207.sql"

localUser="root"
localPassword="Unl3aded"

refreshMageDB() {
	echo "Mage DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBMage}; CREATE DATABASE ${DBMage};"

	echo "Importing Mage DB"
	mysql --user=${localUser} --password=${localPassword} ${DBMage} < ${mageFile}
}

refreshMageDB