# End-to-end-NLP-Project-Implementation

## WordPress Blogging Integration

The repository ships with a **WordPress plugin** (`wordpress-plugin/hate-speech-detector/`) that connects any WordPress blogging website to the NLP hate-speech detection API. Once installed, every new comment submitted on your blog is automatically screened before publication.

### Plugin features

- Sends comment text to the running FastAPI `/predict` endpoint.
- Holds, trashes, or marks as spam any comment whose prediction label is `hate`.
- Configurable confidence threshold (0.0 – 1.0).
- Optional email notification to the site administrator when a comment is flagged.
- Zero external dependencies — uses WordPress's own `wp_remote_post` HTTP API.

### Installation

1. **Start the API** (see *How to run?* section below).
2. Copy `wordpress-plugin/hate-speech-detector/` into your WordPress `wp-content/plugins/` directory.
3. Log in to your WordPress admin dashboard → **Plugins** → activate **Hate Speech Detector**.
4. Go to **Settings → Hate Speech Detector** and set the **API Base URL** to the address of your running FastAPI service (e.g. `http://localhost:8080` or your deployed cloud endpoint).
5. Choose the action for flagged comments and save.

> **Tip:** When deploying to a public server, ensure the FastAPI service is accessible from the WordPress host (or run both on the same machine / Docker network).

### Standalone repository

The plugin is structured as a **self-contained project** and can be pushed to its own GitHub repository at any time:

```bash
# 1. Copy the plugin directory out of this repo
cp -r wordpress-plugin/hate-speech-detector /tmp/hate-speech-detector-wp

# 2. Initialise a new Git repository
cd /tmp/hate-speech-detector-wp
git init
git add .
git commit -m "Initial commit"

# 3. Push to a new GitHub repository
#    (create the empty repo on GitHub first, then:)
git remote add origin https://github.com/<your-username>/hate-speech-detector-wp.git
git branch -M main
git push -u origin main
```

The plugin directory already includes everything needed for a healthy standalone repo:

| File / folder | Purpose |
|---|---|
| `README.md` | Full project documentation |
| `LICENSE` | MIT licence |
| `.gitignore` | PHP / WordPress aware ignore rules |
| `composer.json` | PHP package metadata and dev tooling |
| `CHANGELOG.md` | Version history |
| `.github/workflows/ci.yml` | GitHub Actions CI (PHP syntax check on 7.4 / 8.x) |
| `.github/ISSUE_TEMPLATE/` | Bug report & feature request templates |
| `.github/pull_request_template.md` | PR checklist template |


## Project Workflows

- constants
- config_enity
- artifact_enity
- components
- pipeline
- app.py


## How to run?

```bash
conda create -n hate python=3.8 -y
```

```bash
conda activate hate
```

```bash
pip install -r requirements.txt
```

```bash
python app.py
```


# Gcloud cli
https://dl.google.com/dl/cloudsdk/channels/rapid/GoogleCloudSDKInstaller.exe

```bash
gcloud init
```


## Deployment

1. Setting up circleCI
2. Switch on self hosted runner
3. Create Project
4. Configure EC2
5. config.yml
6. env variables
7. 
