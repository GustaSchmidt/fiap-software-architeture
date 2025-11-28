# Configuração terraform cloud
terraform { 
  cloud { 
    
    organization = "FIAP-SOAT-ORG" 

    workspaces { 
      name = "fiap-soat-database" 
    } 
  } 
}

# Configura o provedor da AWS
provider "aws" {
  region = var.aws_region
}

# 1. Cria a Virtual Private Cloud (VPC)
resource "aws_vpc" "main" {
  cidr_block = var.vpc_cidr_block

  tags = {
    Name = "fiap-soat-vpc"
  }
}

# 2. Cria duas subnets em zonas de disponibilidade diferentes
#    O RDS exige pelo menos duas subnets para alta disponibilidade.
resource "aws_subnet" "db_subnet_a" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.1.0/24"
  availability_zone = "${var.aws_region}a"

  tags = {
    Name = "db-subnet-a"
  }
}

resource "aws_subnet" "db_subnet_b" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.2.0/24"
  availability_zone = "${var.aws_region}b"

  tags = {
    Name = "db-subnet-b"
  }
}

# 3. Cria um grupo de subnets para o RDS
#    Isso informa ao RDS em quais subnets ele pode operar.
resource "aws_db_subnet_group" "default" {
  name       = "fiap-soat-db-subnet-group"
  subnet_ids = [aws_subnet.db_subnet_a.id, aws_subnet.db_subnet_b.id]

  tags = {
    Name = "FIAP SOAT DB Subnet Group"
  }
}

# 4. Cria um grupo de segurança (firewall) DENTRO da VPC
resource "aws_security_group" "db_sg" {
  name        = "db-security-group"
  description = "Permite trafego de entrada para o PostgreSQL de dentro da VPC"
  vpc_id      = aws_vpc.main.id

  # Regra de entrada: permite conexões na porta 5432
  # de qualquer lugar DENTRO da VPC (10.0.0.0/16).
  ingress {
    from_port   = 5432
    to_port     = 5432
    protocol    = "tcp"
    cidr_blocks = [aws_vpc.main.cidr_block]
  }

  # Regra de saída: permite que o DB se conecte a qualquer lugar.
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# 5. Cria a instância do banco de dados RDS
resource "aws_db_instance" "default" {
  identifier          = "fiap-soat-db"
  allocated_storage   = 20 # Espaço em GB
  storage_type        = "gp2"
  engine              = "postgres"
  engine_version      = "15"
  instance_class      = var.db_instance_class
  db_name             = var.db_name
  username            = var.db_username
  password            = var.db_password
  db_subnet_group_name = aws_db_subnet_group.default.name
  vpc_security_group_ids = [aws_security_group.db_sg.id]
  skip_final_snapshot = true 
  publicly_accessible = false
}