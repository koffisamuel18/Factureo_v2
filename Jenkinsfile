pipeline {
    agent any

    stages {

        stage('Checkout Code') {
            steps {
                echo "📥 Copie du projet"
                sh '''
                cp -r /var/www/html/Factureo_v2/* .
                '''
            }
        }

        stage('Terraform Deploy') {
            steps {
                echo "⚙️ Terraform execution"
                sh '''
                cd terraform
                terraform init
                terraform apply -auto-approve
                '''
            }
        }

        stage('Ansible Deploy') {
            steps {
                echo "🚀 Ansible deployment"
                sh '''
                cd ansible-factureo
                ansible-playbook -i inventory.ini playbook.yml
                '''
            }
        }

        stage('Test Application') {
            steps {
                echo "🧪 Test HTTP"
                sh '''
                curl -I http://192.168.126.128/Factureo_v2/
                '''
            }
        }
    }

    post {
        success {
            echo "✅ Pipeline réussi - Factureo déployé"
        }
        failure {
            echo "❌ Échec du pipeline"
        }
    }
}
