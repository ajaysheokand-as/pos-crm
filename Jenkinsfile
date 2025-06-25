pipeline {
    agent any
    environment {
        REMOTE_USER = "ubuntu"
        REMOTE_HOST = "13.204.94.182"
        PROJECT_DIR = "/var/www/html/"
        SSH_CREDENTIALS_ID = 'pos_ssh_key'
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

 