from flask import Flask
from flask_php import PHP

app = Flask(__name__)
php = PHP(app)

@app.route("/")
def index():
    return php.render_template('memeg_bot.php')

if __name__ == "__main__":
    app.run(port=8080)
