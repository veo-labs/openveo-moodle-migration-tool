# 1.1.0 / YYYY-MM-DD

## BREAKING CHANGES

- Drop support for OpenVeo Core &lt; 8.2.0
- Drop support for OpenVeo Publish &lt; 10.2.0
- OpenVeo web service client for Moodle now requires "Get users" scope

## NEW FEATURES

- Automatically assign the migrated video to the OpenVeo user with the same email address as the Moodle user holding the file
- New configuration is available to be able to change the format of migrated video names on OpenVeo. Several tokens are available to help customize the names format from contextual information retrieved at the time of migration
- A description has been added to migrated videos on OpenVeo containing information about where the original video and its aliases appeared at the time of migration

# 1.0.1 / 2018-11-15

## BUG FIXES

- Fix configuration page which didn't work at all

# 1.0.0 / 2018-10-17

## NEW FEATURES

- Adds an OpenVeo Migration Tool plugin to Moodle administration tools to migrate Moodle videos to OpenVeo Publish
