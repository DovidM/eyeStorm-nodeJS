

const cp = require('child_process');


function installModules() {

    const commands = [
        ['npm', ['--prefix', './client', 'install']]
    ];

    commands.forEach((command, i) => {

        console.log(`Installing npm modules for ${command[1][1]}...`);

        const node_modulesInstallation = cp.spawn(command[0], command[1]);

        node_modulesInstallation.stdout.on('data', function(data) {
            console.log(data.toString());
        });

        node_modulesInstallation.on('close', function(code, signal) {

            console.log("Completed installation for", command[1][1]);

            if (i === 1) {
                initializeDatabase();
            }
        });
    });
}


async function initializeDatabase() {

    console.log('Initializing database...');

    let DB = require('./backend/dist/config');
    DB = DB.default.DB
    const fs = require.main.require('./backend/node_modules/fs-extra'); // change to something else
    const mysql = require.main.require('./backend/node_modules/mysql2/promise');

    const connection = mysql.createConnection({
        host: DB.HOST,
        port: DB.PORT,
        user: DB.USER,
        password: DB.PASS,
        multipleStatements: true
    });

    const asyncDB = await connection;

    fs.readFile('schema.sql', 'utf8', async (err, data) => {

        await asyncDB.query(`CREATE DATABASE IF NOT EXISTS ${DB.NAME}`);
        await asyncDB.query(`USE ${DB.NAME}`);

        await asyncDB.query(data);

        createAdmin(asyncDB);
    });
}

async function createAdmin(asyncDB) {

    const bcrypt = require.main.require('./backend/node_modules/bcrypt');

    const args = process.argv.slice(2);

    const email = args[args.indexOf('-email') + 1];
    const password = args[args.indexOf('-password') + 1];

    if (!(email && password)) {

        console.error("\nFailure: Incorrect Usage. The command should follow the following format: \n"+
        "node ./install-all.js -email your_email -password your_password\n");
        process.exit(1);
    }

    if ((await asyncDB.query('SELECT * FROM users WHERE username = ?', ['admin']))[0][0]) {

        console.log("Admin already exists!");
    }
    else {

        const hashedPassword = (await bcrypt.hash(password, 10)).replace(/^\$2a/, '$2y'); // replacing so compatible with php's password_hash

        asyncDB.query(`INSERT INTO users (username, f_name, m_name, l_name, password, level, email, notifications)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
                                    ['admin', 'Admin', null, 'Account', hashedPassword,
                                    3, email, 1]);

        console.log(`Admin created with password ${password} and email ${email}`);
    }

    process.exit();
}

try {
    installModules();
}
catch(error) {
    console.err(error + "\n\n Please run the command again.");
}