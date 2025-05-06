pipeline {
    agent any
    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "15.206.232.129"
        PROJECT_DIR = "/var/www/html/tejasloan-backend"
        SSH_CREDENTIALS_ID = 'tejasloan_backend_ssh_key'
    }
     stages {
        stage('Pull Latest Code') {
            steps {
                sshagent (credentials: [env.SSH_CREDENTIALS_ID]) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} '
                        cd ${PROJECT_DIR} &&
                        git stash &&
                        git pull origin main
                        '
                    """
                }
            }
        }
    }
}

 