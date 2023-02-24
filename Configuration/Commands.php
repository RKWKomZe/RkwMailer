<?php

return [
    'rkw_mailer:send' => [
        'class' => \RKW\RkwMailer\Command\SendCommand::class,
        'schedulable' => true,
    ],
    'rkw_mailer:analyseStatistics' => [
        'class' => \RKW\RkwMailer\Command\AnalyseStatisticsCommand::class,
        'schedulable' => true,
    ],
    'rkw_mailer:analyseBounceMails' => [
        'class' => \RKW\RkwMailer\Command\AnalyseBounceMailsCommand::class,
        'schedulable' => true,
    ],
    'rkw_mailer:processBounceMails' => [
        'class' => \RKW\RkwMailer\Command\ProcessBounceMailsCommand::class,
        'schedulable' => true,
    ],
    'rkw_mailer:cleanup' => [
        'class' => \RKW\RkwMailer\Command\CleanupCommand::class,
        'schedulable' => true,
    ],
];
