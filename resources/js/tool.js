import Main from './pages/Main'
import Configure from './pages/Configure'
import Preview from './pages/Preview'
import Review from './pages/Review'

Nova.booting((app, store) => {
  Nova.inertia('CsvImport/Main', Main)
  Nova.inertia('CsvImport/Configure', Configure)
  Nova.inertia('CsvImport/Preview', Preview)
  Nova.inertia('CsvImport/Review', Review)
})
