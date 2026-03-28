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
