terraform {
  cloud {
    organization = "FIAP-SOAT-ORG"
    workspaces {
      name = "fiap-soat-cluster" 
    }
  }
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = "us-east-1"
}

# --- Ler dados do Workspace de Banco de Dados ---
# Isso permite pegar o ID da VPC e Subnets criados no outro passo
data "terraform_remote_state" "database" {
  backend = "remote"
  config = {
    organization = "FIAP-SOAT-ORG"
    workspaces = {
      name = "fiap-soat-database"
    }
  }
}

# --- Cluster EKS ---
module "eks" {
  source  = "terraform-aws-modules/eks/aws"
  version = "~> 19.0"

  cluster_name    = "fiap-soat-cluster"
  cluster_version = "1.30"

  vpc_id     = data.terraform_remote_state.database.outputs.vpc_id
  subnet_ids = data.terraform_remote_state.database.outputs.subnet_ids

  cluster_endpoint_public_access = true

  eks_managed_node_groups = {
    default = {
      min_size     = 1
      max_size     = 2
      desired_size = 1
      instance_types = ["t3.small"]
      capacity_type  = "ON_DEMAND"
      network_interfaces = [{
        associate_public_ip_address = true
      }]
    }
  }

  tags = {
    Environment = "prod"
    Project     = "FIAP-SOAT"
  }
}

# --- Repositório ECR (Docker Registry) ---
# Como atualizo toda vez que faço push pode ir pro saco sem dó
resource "aws_ecr_repository" "app_repo" {
  name                 = "fiap-soat-app"
  image_tag_mutability = "MUTABLE"
  force_delete         = true

  image_scanning_configuration {
    scan_on_push = true
  }
}

output "ecr_repository_url" {
  value = aws_ecr_repository.app_repo.repository_url
}