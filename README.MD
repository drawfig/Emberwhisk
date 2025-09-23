# Emberwhisk

## About Emberwhisk
Emberwhisk is a robust PHP and OpenSwoole framework designed to streamline the development of high-performance WebSocket applications. It's built on a simple philosophy: eliminate the tedious boilerplate so you can focus on your application's unique logic. With Emberwhisk, you can build powerful, real-time applications faster and with less effort.

Here's how we handle the heavy lifting:

- Connection and Lifecycle Management: Emberwhisk abstracts away the complexity of raw sockets, automatically managing connections from the initial handshake to graceful disconnections, while also providing the flexibility for manual control when needed.

- Message Routing and Event Dispatching: Our intuitive routing system allows you to effortlessly map incoming messages to the right application handler or service, making your codebase clean and organized.

- Built-in Error Handling: We provide a stable, opinionated system for catching and handling exceptions, ensuring your server remains reliable under pressure.

To further accelerate your workflow, Emberwhisk is paired with [WAND](https://github.com/drawfig/WAND), a development toolkit. WAND provides a rich command-line interface for debugging and managing your server without ever leaving your terminal.

Join us on our [Discord server.](https://discord.gg/gtwuf2A4Hq)

## How to install Emberwhisk

It is **highly recommended** to use the [**WAND**](https://github.com/drawfig/WAND) development tool for installation. WAND allows you to easily install, configure, and run the Emberwhisk server in a development environment while also offering other tools for working with the framework.

However, if you prefer to install the Emberwhisk framework directly, you must first have the following dependencies installed:

* **PHP:** `cli`, `dev` and `PEAR/PECL` packages
* **OpenSwoole**
* **PHP:** `sqlite` and `PDO` modules
* **Composer**

---

### Manual Installation

1.  **Clone the repository:**
    ```
    git clone https://github.com/drawfig/Emberwhisk.git
    ```

2.  **Install dependencies with Composer:**
    ```
    cd Emberwhisk
    composer install
    ```

3.  **Create your configuration file:**
    > Move into the `src` directory and use the `dotenv.example` file to create a configuration for your environment. For example, to create a configuration for the development environment:
    ```
    cd src
    cp dotenv.example .env.dev
    ```
    Then, open the new `.env.dev` file in a text editor to add your server's configuration settings.

4.  **Start the server:**
    ```
    php run.php dev
    ```
    > **Note:** The optional argument at the end (`dev` in this case) determines which environment the server starts in, using the corresponding `.env` file. If left empty, the server will default to the `local` environment.

## How to Contribute

Emberwhisk is an open-source project, and we welcome contributions from the community! If you'd like to get involved, here are a few ways you can help:
- **Report Bugs:** If you find a bug or an issue, please open an issue on the GitHub repository.
- **Submit Code:** Have an idea for a new feature or a bug fix? Feel free to fork the repository and submit a pull request.
- **Get in Touch:** For general questions, feature discussions, or to just chat about the project, you can contact me on Discord at `drawfig` or by joining [our server.](https://discord.gg/gtwuf2A4Hq).

Your contributions are greatly appreciated and help make Emberwhisk better for everyone.

## License
Emberwhisk is open-source software licensed under the GNU General Public License v3.0 (GPL-3.0). A copy of the license is included in the root directory of this project.



