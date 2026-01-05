# Configuração terraform cloud
terraform { 
  cloud { 
    
    organization = "FIAP-SOAT-ORG" 

    workspaces { 
      name = "fiap-soat-database" 
    } 
  } 
}

provider "aws" {
  region = var.aws_region
}

# 1. Rede
resource "aws_vpc" "main" {
  cidr_block           = "10.0.0.0/16"
  enable_dns_hostnames = true
  enable_dns_support   = true

  tags = {
    Name = "fiap-soat-vpc"
  }
}

resource "aws_route_table" "public_rt" {
  vpc_id = aws_vpc.main.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.gw.id
  }

  tags = {
    Name = "fiap-soat-public-rt"
  }
}

#O RDS exige pelo menos duas subnets para alta disponibilidade.
resource "aws_subnet" "db_subnet_a" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.1.0/24"
  availability_zone = "${var.aws_region}a"
  map_public_ip_on_launch = true

  tags = {
    Name = "db-subnet-a"
  }
}

resource "aws_subnet" "db_subnet_b" {
  vpc_id            = aws_vpc.main.id
  cidr_block        = "10.0.2.0/24"
  availability_zone = "${var.aws_region}b"
  map_public_ip_on_launch = true

  tags = {
    Name = "db-subnet-b"
  }
}

resource "aws_route_table_association" "public_assoc_a" {
  subnet_id      = aws_subnet.db_subnet_a.id
  route_table_id = aws_route_table.public_rt.id
}

resource "aws_route_table_association" "public_assoc_b" {
  subnet_id      = aws_subnet.db_subnet_b.id
  route_table_id = aws_route_table.public_rt.id
}


resource "aws_db_subnet_group" "default" {
  name       = "fiap-soat-db-subnet-group"
  subnet_ids = [aws_subnet.db_subnet_a.id, aws_subnet.db_subnet_b.id]

  tags = {
    Name = "FIAP SOAT DB Subnet Group"
  }
}


resource "aws_security_group" "db_sg" {
  name        = "db-security-group"
  description = "Permite acesso ao RDS"
  vpc_id      = aws_vpc.main.id

  # Entrada: Apenas dentro da VPC (Lambda e EKS acessam)
  ingress {
    from_port   = 5432
    to_port     = 5432
    protocol    = "tcp"
    cidr_blocks = [aws_vpc.main.cidr_block]
  }

  # Saída: Liberada (importante para atualizações/manutenção)
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
}

# --- RDS (Banco de Dados) ---
resource "aws_db_instance" "default" {
  allocated_storage    = 20
  storage_type         = "gp2"
  engine               = "postgres"
  engine_version       = "16.3"
  instance_class       = "db.t3.micro"
  db_name              = "fiap_soat"
  username             = "fiap_db_user_main"
  password             = var.db_password
  parameter_group_name = "default.postgres16"
  skip_final_snapshot  = true
  publicly_accessible  = false # Banco seguro, não acessível da rua

  vpc_security_group_ids = [aws_security_group.db_sg.id]
  db_subnet_group_name   = aws_db_subnet_group.default.name
}