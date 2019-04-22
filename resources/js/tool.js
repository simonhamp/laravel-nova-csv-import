Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'csv-import',
            path: '/csv-import',
            component: require('./components/Main'),
        },
        {
            name: 'csv-import-preview',
            path: '/csv-import/preview/:file',
            component: require('./components/Preview'),
            props: route => {
                return {
                    file: route.params.file,
                }
            },
        },
        {
            name: 'csv-import-review',
            path: '/csv-import/review/:file',
            component: require('./components/Review'),
            props: route => {
                return {
                    file: route.params.file,
                }
            },
        },
    ])
})
