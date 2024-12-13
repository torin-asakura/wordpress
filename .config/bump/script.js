const { execSync } = require('child_process')
const { readFileSync, writeFileSync } = require('fs')
const { join } = require('path')

const searchDir = join(__dirname, '../../')

console.log(`Searching for entries in ${searchDir}`)

const rawEntries = execSync(`find ${searchDir} -name style.css -not -path "${searchDir}themes/yootheme/*" `)
const entries = Buffer.from(rawEntries).toString('utf-8').split('\n').filter(Boolean)

const bumpVersion = (version) => {
  const [major, minor, patch] = version.split('.').map(Number)

  if (patch === 10) {
    return [major, minor + 1, 0].join('.')
  }

  if (minor === 10) {
    return [major + 1, 0, 0].join('.')
  }

  return [major, minor, patch + 1].join('.')
}

console.log(`Found ${entries.length} entries`)

for (const entry of entries) {
  const content = Buffer.from(readFileSync(entry)).toString('utf-8')
  const manifest = new Map(content.replaceAll('/*', '').replaceAll('*/','').split('\n').filter(Boolean).map(str => str.trim().split(':').map(s => s.trim())))
  const version = manifest.get('Version')

  console.log(`Bumping ${entry} from ${version} to ${bumpVersion(version)}`)

  const newContent = content.replace(version, bumpVersion(version))
  writeFileSync(entry, newContent)
}

console.log('Done')
