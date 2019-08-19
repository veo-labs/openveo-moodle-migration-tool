# OpenVeo Moodle Migration Tool

OpenVeo Moodle Migration Tool is a Moodle Admin tools plugin which migrates videos, hosted on Moodle, in [OpenVeo Publish](https://github.com/veo-labs/openveo-publish).

# Getting Started

## Prerequisites

- PHP >=7
- Moodle version >=3.4.0
- [Openveo](https://github.com/veo-labs/openveo-core) >=5.1.1
- [Openveo Publish plugin](https://github.com/veo-labs/openveo-publish) >=7.1.0
- [OpenVeo Moodle API plugin](https://github.com/veo-labs/openveo-moodle-api) >=1.0.0
- [OpenVeo Moodle Repository plugin](https://github.com/veo-labs/openveo-moodle-repository) >=1.0.0
- Make sure OpenVeo Moodle API plugin is configured
- Make sure OpenVeo Moodle Repository plugin is configured
- OpenVeo web service client for Moodle must have scopes **Add video**, **Get video platforms**, **Publish videos**, **Delete videos** and **Get users**
- Moodle file system must be the default one, which means configuration variable **alternative_file_system_class** must not be used

## Installation

- Download zip file corresponding to the latest stable version of the OpenVeo Moodle Migration Tool plugin
- Unzip it and rename **openveo-moodle-migration-tool-\*** directory into **openveo_migration**
- Move your **openveo_migration** folder into **MOODLE_ROOT_PATH/admin/tool/** where MOODLE_ROOT_PATH is your Moodle installation folder
- In your Moodle site (as admin) go to **Site administration > Notifications**: you should get a message saying the plugin is installed
- Configure how migration must operate in **Site administration > Plugins > Admin tools > OpenVeo Migration Tool settings**
- Configure when automatic migration must start in **Site administration > Server > Scheduled tasks > Migrate Moodle videos to OpenVeo**
- OpenVeo web service client for Moodle must have scopes **Publish videos**, **Get videos**, **Delete videos**, **Add video** and **Get video platforms**

If you experience troubleshooting during installation, please refer to the [Moodle](https://docs.moodle.org) installation plugin documentation.

## Launch migration

If automatic migration has been activated (**Site administration > Plugins > Admin tools > OpenVeo Migration Tool settings**) and **Migrate Moodle videos to OpenVeo** (**Site administration > Server > Scheduled tasks > Migrate Moodle videos to OpenVeo**) scheduled task is enabled, migration will start at the task time.

You can also decide to launch the migration manually using command line (from Moodle root directory):

        php admin/tool/task/cli/schedule_task.php --execute=\\tool_openveo_migration\\task\\migrate

# Troubleshooting

## Videos are not migrated

If you installed non-native plugins which make use of Moodle Form API with **editor** or **filemanager** fields, videos added through these plugins might not be able to be migrated to OpenVeo. Only videos added through fields supporting FILE_EXTERNAL or FILE_REFERENCE can be migrated but OpenVeo Migration Tool can't find this information by itself because it is hardcoded in Moodle. Consequently, for each usage of a form field of type **editor** or **filemanager**, OpenVeo Migration Tool needs to know the supporting methods (FILE_EXTERNAL, FILE_REFERENCE, etc.).

OpenVeo Migration Tool knows these information for a native Moodle installation but don't know about non-native plugins. These information are stored in plugin's configuration (**Site administration > Plugins > Admin tools > OpenVeo Migration Tool settings**). Each line corresponds to a field of type **editor** or **filemanager** within Moodle. Columns are separated by pipes just like in a CSV file. You can add other fields (from non-native plugins) here by adding new lines.

Columns are: **component|filearea|supportingmethods** with:

- **component**: The name of the plugin holding the file
- **filearea**: The area the file belongs to
- **supportingmethods**: A number representing the list of supported methods (FILE_EXTERNAL=1, FILE_INTERNAL=2, FILE_REFERENCE=4, FILE_CONTROLLED_LINK=8). If field supports more than one method, a number representing several methods can be computed by a binary OR (e.g. FILE_EXTERNAL|FILE_INTERNAL|FILE_REFERENCE|FILE_CONTROLLED_LINK=15)

**Nb:** Sadly you will have to read the source code of the plugin to find these information for fields of type **editor** and **filemanager**.

# Contributors

Maintainer: [Veo-Labs](http://www.veo-labs.com/)

# License

[GPL3](http://www.gnu.org/licenses/gpl.html)
