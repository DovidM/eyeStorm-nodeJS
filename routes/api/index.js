const router = require('express').Router();
const Utilities = require('../../controller/classes/Utilities');

const previews = require('./previews');
const story = require('./story');
const userStatus = require('./userStatus');

// for jwt getting and setting
router.use(function (req, res, next) {

    Utilities.req = req;
    Utilities.res = res;
    next();
});

// split up route handling
router.use('/previews', previews);
router.use('/story', story);
router.use('/userStatus', userStatus);

module.exports = router;