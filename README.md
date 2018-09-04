# OpenVeo Moodle Migration Tool

OpenVeo Moodle Migration Tool is a Moodle Admin tools plugin which migrates videos, hosted on Moodle, in [OpenVeo Publish](https://github.com/veo-labs/openveo-publish).

# Getting Started

## Prerequisites

- Moodle version >=3.4.0
- [Openveo](https://github.com/veo-labs/openveo-core) >=5.1.1
- [Openveo Publish plugin](https://github.com/veo-labs/openveo-publish) >=7.1.0
- [OpenVeo Moodle API plugin](https://github.com/veo-labs/openveo-moodle-api) >=1.0.0
- Make sure OpenVeo Moodle API plugin is configured

## Installation

- Download zip file corresponding to the latest stable version of the OpenVeo Moodle Migration Tool plugin
- Unzip it and rename **openveo-moodle-migration-tool-\*** directory into **openveo_migration**
- Move your **openveo_migration** folder into **MOODLE_ROOT_PATH/admin/tool/** where MOODLE_ROOT_PATH is your Moodle installation folder
- In your Moodle site (as admin) go to **Site administration > Notifications**: you should get a message saying the plugin is installed
- Configure how migration must operate in **Site administration > Plugins > Admin tools > OpenVeo Migration Tool settings**

If you experience troubleshooting during installation, please refer to the [Moodle](https://docs.moodle.org) installation plugin documentation.

# Contributors

Maintainer: [Veo-Labs](http://www.veo-labs.com/)

# License

[GPL3](http://www.gnu.org/licenses/gpl.html)
